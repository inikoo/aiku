<?php

namespace App\Actions\CRM\Appointment;

use App\Enums\CRM\Appointment\AppointmentSourceEnum;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Appointment;
use App\Models\CRM\AppointmentType;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;
use Throwable;

/**
 * Brings the bookings still to come from a Calendly "Export events" CSV into the shop's
 * appointments, so the website stops offering their times. Visitors are not emailed: Calendly
 * already confirmed them. A booking cancelled in Calendly, already past, or imported before is
 * skipped, so the same file can be run again.
 */
class ImportCalendlyAppointments
{
    use AsAction;

    public string $commandSignature = 'appointments:import-calendly
        {shop : Slug of the shop}
        {file : Path to the Calendly events export (CSV)}
        {--type= : Id or slug of the appointment type the bookings become}
        {--timezone= : Timezone of the times in the file, the shop timezone when left out}
        {--dry-run : Show what would be imported without saving anything}';

    public string $commandDescription = 'Import upcoming bookings from a Calendly events export as appointments';

    /**
     * @return array{imported: array<int, array<string, mixed>>, skipped: array<int, array<string, mixed>>, failed: array<int, array<string, mixed>>}
     */
    public function handle(Shop $shop, AppointmentType $appointmentType, string $path, ?string $timezone = null, bool $dryRun = false): array
    {
        $shopTimezone = $shop->timezone?->name ?? 'UTC';
        $timezone     = $timezone ?: $shopTimezone;
        $result       = ['imported' => [], 'skipped' => [], 'failed' => []];

        foreach ($this->rows($path) as $rowNumber => $row) {
            $summary = ['row' => $rowNumber, 'start' => Arr::get($row, 'Start Date & Time'), 'name' => Arr::get($row, 'Invitee Name')];

            if (Str::lower(trim((string) Arr::get($row, 'Canceled'))) === 'true') {
                $result['skipped'][] = [...$summary, 'reason' => __('Cancelled in Calendly')];
                continue;
            }

            try {
                $startsAt = Carbon::createFromFormat('Y-m-d H:i', trim((string) Arr::get($row, 'Start Date & Time')), $timezone);
            } catch (Throwable) {
                $result['failed'][] = [...$summary, 'reason' => __('The start time is not in the format YYYY-MM-DD HH:MM')];
                continue;
            }

            if ($startsAt->isPast()) {
                $result['skipped'][] = [...$summary, 'reason' => __('Already took place')];
                continue;
            }

            $inviteeUuid = trim((string) Arr::get($row, 'Invitee UUID'));
            if ($inviteeUuid && Appointment::where('shop_id', $shop->id)->where('data->calendly_invitee_uuid', $inviteeUuid)->exists()) {
                $result['skipped'][] = [...$summary, 'reason' => __('Imported before')];
                continue;
            }

            if ($dryRun) {
                $result['imported'][] = $summary;
                continue;
            }

            try {
                $appointment = StoreAppointment::make()->action(
                    $shop,
                    [
                        'appointment_type_id' => $appointmentType->id,
                        'starts_at'           => $startsAt->copy()->setTimezone($shopTimezone)->format('Y-m-d H:i'),
                        'contact_name'        => Str::limit(trim((string) Arr::get($row, 'Invitee Name')) ?: __('Calendly visitor'), 250, ''),
                        'email'               => $this->email(Arr::get($row, 'Invitee Email')),
                        'phone'               => $this->phone(Arr::get($row, 'Text Reminder Number')),
                        'number_visitors'     => min(100, 1 + count($this->guests($row))),
                        'notes'               => $this->notes($row),
                        'source'              => AppointmentSourceEnum::IMPORTED->value,
                        'state'               => AppointmentStateEnum::ACCEPTED->value,
                    ],
                    notifyVisitor: false,
                    data: array_filter([
                        'imported_from'         => 'calendly',
                        'calendly_event_uuid'   => trim((string) Arr::get($row, 'Event UUID')) ?: null,
                        'calendly_invitee_uuid' => $inviteeUuid ?: null,
                    ])
                );
                $this->keepBookedAt($appointment, Arr::get($row, 'Event Created Date & Time'), $timezone);
                $result['imported'][] = [...$summary, 'appointment_id' => $appointment->id];
            } catch (ValidationException $exception) {
                $result['failed'][] = [...$summary, 'reason' => collect($exception->errors())->flatten()->implode(' ')];
            }
        }

        return $result;
    }

    /**
     * Calendly answers can run over several lines inside quotes, so the file is read as CSV rather
     * than line by line. Rows are keyed by their number in the file, the header being row 1.
     *
     * @return array<int, array<string, string>>
     */
    public function rows(string $path): array
    {
        $handle = @fopen($path, 'r');
        if (!$handle) {
            throw new RuntimeException(__('Cannot read :path', ['path' => $path]));
        }

        $header = fgetcsv($handle, escape: '');
        if (!$header) {
            fclose($handle);

            return [];
        }
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $header    = array_map('trim', $header);

        $missing = array_diff(['Invitee Name', 'Start Date & Time', 'Canceled'], $header);
        if ($missing) {
            fclose($handle);
            throw new RuntimeException(__('This is not a Calendly events export, it has no :columns column', ['columns' => implode(', ', $missing)]));
        }

        $rows      = [];
        $rowNumber = 1;
        while (($values = fgetcsv($handle, escape: '')) !== false) {
            $rowNumber++;
            if ($values === [null]) {
                continue;
            }
            $rows[$rowNumber] = array_combine($header, array_pad(array_slice($values, 0, count($header)), count($header), ''));
        }
        fclose($handle);

        return $rows;
    }

    /**
     * The visitor was confirmed by Calendly when they booked, not when the file was imported. Kept
     * that way, an appointment imported inside the reminder window still gets its reminder, which
     * is held back only for visitors confirmed by Aiku that recently.
     */
    private function keepBookedAt(Appointment $appointment, ?string $createdAt, string $timezone): void
    {
        try {
            $bookedAt = Carbon::createFromFormat('Y-m-d H:i', trim((string) $createdAt), $timezone);
        } catch (Throwable) {
            return;
        }

        if ($bookedAt->isPast()) {
            $appointment->forceFill(['accepted_at' => $bookedAt->utc()])->saveQuietly();
        }
    }

    private function email(?string $email): ?string
    {
        $email = trim((string) $email);

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * Calendly writes the number with a leading apostrophe so spreadsheets keep it as text.
     */
    private function phone(?string $phone): ?string
    {
        $phone = trim(ltrim(trim((string) $phone), "'"));

        return strlen(preg_replace('/\D+/', '', $phone)) > 4 ? $phone : null;
    }

    /**
     * @return array<int, string>
     */
    private function guests(array $row): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) Arr::get($row, 'Guest Email(s)')))));
    }

    private function notes(array $row): string
    {
        $notes = [__('Imported from Calendly: :event', ['event' => trim((string) Arr::get($row, 'Event Type Name'))])];

        for ($question = 1; Arr::has($row, 'Question '.$question); $question++) {
            $answer = trim((string) Arr::get($row, 'Response '.$question));
            if ($answer !== '') {
                $notes[] = trim((string) Arr::get($row, 'Question '.$question))."\n".$answer;
            }
        }

        if ($guests = $this->guests($row)) {
            $notes[] = __('Guests').': '.implode(', ', $guests);
        }

        return Str::limit(implode("\n\n", $notes), 4990, '');
    }

    public function asCommand(Command $command): int
    {
        $shop = Shop::where('slug', $command->argument('shop'))->first();
        if (!$shop) {
            $command->error('No shop with slug '.$command->argument('shop'));

            return 1;
        }

        $type            = (string) $command->option('type');
        $appointmentType = $shop->appointmentTypes()
            ->where(fn ($query) => $query->where('slug', $type)->when(ctype_digit($type), fn ($query) => $query->orWhere('id', (int) $type)))
            ->first();
        if (!$appointmentType) {
            $command->error('Pass --type with the id or slug of one of these appointment types: '.$shop->appointmentTypes()->get()->map(fn (AppointmentType $type) => $type->id.' '.$type->slug)->implode(', '));

            return 1;
        }

        $timezone = $command->option('timezone');
        if ($timezone && !in_array($timezone, timezone_identifiers_list(), true)) {
            $command->error('Unknown timezone '.$timezone.', use a name like Europe/London');

            return 1;
        }

        try {
            $result = $this->handle($shop, $appointmentType, (string) $command->argument('file'), $timezone, (bool) $command->option('dry-run'));
        } catch (RuntimeException $exception) {
            $command->error($exception->getMessage());

            return 1;
        }

        foreach (['imported' => $command->option('dry-run') ? 'Would import' : 'Imported', 'skipped' => 'Skipped', 'failed' => 'Failed'] as $key => $title) {
            if ($result[$key]) {
                $command->info($title.' ('.count($result[$key]).')');
                $command->table(array_keys($result[$key][0]), $result[$key]);
            }
        }

        $command->info(sprintf(
            '%s %d, skipped %d, failed %d, as %s',
            $command->option('dry-run') ? 'Would import' : 'Imported',
            count($result['imported']),
            count($result['skipped']),
            count($result['failed']),
            $appointmentType->name
        ));

        return $result['failed'] ? 1 : 0;
    }
}
