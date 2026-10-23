<?php

use App\Actions\Comms\Email\SendAppointmentEmail;
use App\Actions\CRM\Appointment\ImportCalendlyAppointments;
use App\Actions\CRM\AppointmentType\StoreAppointmentType;
use App\Enums\CRM\Appointment\AppointmentSourceEnum;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Enums\CRM\AppointmentType\AppointmentTypeMeetingModeEnum;
use App\Models\CRM\Appointment;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Decorators\JobDecorator;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {
    list($this->organisation, $this->user, $this->shop) = createShop();

    $this->timezone        = $this->shop->timezone?->name ?? 'UTC';
    $this->appointmentType = StoreAppointmentType::make()->action($this->shop, [
        'name'                => 'Calendly import '.Str::random(6),
        'meeting_mode'        => AppointmentTypeMeetingModeEnum::STORE_VISIT->value,
        'location'            => 'Showroom, Sheffield',
        'duration_minutes'    => 60,
        'min_notice_hours'    => 0,
        'booking_window_days' => 60,
        'capacity_per_slot'   => 3,
        'availability'        => ['weekly' => array_fill_keys(range(1, 7), [['from' => '08:30', 'to' => '15:00']])],
    ]);

    $this->csv = function (array $rows): string {
        $header = ['Invitee Name', 'Invitee Email', 'Text Reminder Number', 'Event Type Name', 'Start Date & Time', 'End Date & Time', 'Event Created Date & Time', 'Canceled', 'Question 1', 'Response 1', 'Guest Email(s)', 'Event UUID', 'Invitee UUID'];
        $path   = tempnam(sys_get_temp_dir(), 'calendly').'.csv';
        $handle = fopen($path, 'w');
        fputcsv($handle, $header, escape: '');
        foreach ($rows as $row) {
            fputcsv($handle, array_map(fn (string $column) => $row[$column] ?? '', $header), escape: '');
        }
        fclose($handle);

        return $path;
    };

    $this->row = fn (array $overrides = []) => [
        'Invitee Name'         => 'Calendly visitor '.Str::random(6),
        'Invitee Email'        => 'calendly-'.Str::lower(Str::random(8)).'@example.com',
        'Text Reminder Number' => "'+44 7700 900123",
        'Event Type Name'      => 'Showroom Appointment',
        'Start Date & Time'         => now($this->timezone)->addDays(5)->format('Y-m-d').' 10:30',
        'Event Created Date & Time' => now($this->timezone)->subDays(4)->format('Y-m-d').' 16:30',
        'Canceled'                  => 'false',
        'Event UUID'           => (string) Str::uuid(),
        'Invitee UUID'         => (string) Str::uuid(),
        ...$overrides,
    ];
});

test('upcoming Calendly bookings become appointments without emailing the visitors again', function () {
    Queue::fake();

    $booking = ($this->row)([
        'Question 1'     => 'Please share anything that will help prepare for our meeting.',
        'Response 1'     => "We run a gift shop.\nWe want bath products.",
        'Guest Email(s)' => 'guest-one@example.com, guest-two@example.com',
    ]);
    $path = ($this->csv)([
        $booking,
        ($this->row)(['Canceled' => 'true']),
        ($this->row)(['Start Date & Time' => now($this->timezone)->subDay()->format('Y-m-d').' 10:30']),
        ($this->row)(['Start Date & Time' => 'next Tuesday']),
    ]);

    $result      = ImportCalendlyAppointments::make()->handle($this->shop, $this->appointmentType, $path);
    $appointment = Appointment::where('data->calendly_invitee_uuid', $booking['Invitee UUID'])->first();

    expect($result['imported'])->toHaveCount(1)
        ->and(collect($result['skipped'])->pluck('reason')->all())->toBe(['Cancelled in Calendly', 'Already took place'])
        ->and($result['failed'])->toHaveCount(1)
        ->and($appointment->shop_id)->toBe($this->shop->id)
        ->and($appointment->appointment_type_id)->toBe($this->appointmentType->id)
        ->and($appointment->source)->toBe(AppointmentSourceEnum::IMPORTED)
        ->and($appointment->state)->toBe(AppointmentStateEnum::ACCEPTED)
        ->and($appointment->starts_at->copy()->setTimezone($this->timezone)->format('Y-m-d H:i'))->toBe($booking['Start Date & Time'])
        ->and($appointment->email)->toBe($booking['Invitee Email'])
        ->and($appointment->phone)->toBe('+44 7700 900123')
        ->and($appointment->number_visitors)->toBe(3)
        ->and($appointment->notes)->toContain("We run a gift shop.\nWe want bath products.")
        ->and($appointment->notes)->toContain('guest-two@example.com')
        ->and($appointment->data['imported_from'])->toBe('calendly')
        ->and($appointment->accepted_at->copy()->setTimezone($this->timezone)->format('Y-m-d H:i'))->toBe($booking['Event Created Date & Time']);

    Queue::assertNotPushed(JobDecorator::class, fn ($job) => $job->displayName() === SendAppointmentEmail::class);

    $again = ImportCalendlyAppointments::make()->handle($this->shop, $this->appointmentType, $path);

    expect($again['imported'])->toHaveCount(0)
        ->and(collect($again['skipped'])->pluck('reason'))->toContain('Imported before')
        ->and(Appointment::where('data->calendly_invitee_uuid', $booking['Invitee UUID'])->count())->toBe(1);
});

test('a dry run of a Calendly import saves nothing', function () {
    $booking = ($this->row)();
    $path    = ($this->csv)([$booking]);

    $this->artisan('appointments:import-calendly', [
        'shop'      => $this->shop->slug,
        'file'      => $path,
        '--type'    => $this->appointmentType->id,
        '--dry-run' => true,
    ])->assertSuccessful();

    expect(Appointment::where('data->calendly_invitee_uuid', $booking['Invitee UUID'])->exists())->toBeFalse();
});
