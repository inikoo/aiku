<?php

namespace App\Actions\CRM\Appointment;

use App\Actions\Comms\Email\SendAppointmentEmail;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Models\CRM\Appointment;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * The visitor of an accepted appointment is emailed once, 12 hours before. A visitor whose
 * confirmation or new time reached them inside those 12 hours already has the details fresh, so
 * the appointment is left alone.
 */
class RemindUpcomingAppointments
{
    use AsAction;

    public const int REMIND_HOURS_BEFORE = 12;

    public string $commandSignature = 'appointments:remind';

    public function handle(): int
    {
        $reminded = 0;

        Appointment::where('state', AppointmentStateEnum::ACCEPTED)
            ->whereNull('reminder_sent_at')
            ->whereNotNull('email')
            ->where('starts_at', '>', now())
            ->where('starts_at', '<=', now()->addHours(self::REMIND_HOURS_BEFORE))
            ->where(fn (Builder $query) => $query->whereNull('accepted_at')
                ->orWhereRaw("accepted_at <= starts_at - interval '".self::REMIND_HOURS_BEFORE." hours'"))
            ->eachById(function (Appointment $appointment) use (&$reminded) {
                $appointment->forceFill(['reminder_sent_at' => now()])->saveQuietly();

                SendAppointmentEmail::dispatch($appointment->id, OutboxCodeEnum::APPOINTMENT_REMINDER->value);
                $reminded++;
            });

        return $reminded;
    }

    public static function isInsideReminderWindow(Appointment $appointment): bool
    {
        return $appointment->starts_at->lessThanOrEqualTo(now()->addHours(self::REMIND_HOURS_BEFORE));
    }

    public function asCommand(Command $command): int
    {
        $command->info($this->handle().' appointment reminders sent');

        return 0;
    }
}
