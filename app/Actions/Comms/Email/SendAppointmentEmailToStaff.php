<?php

namespace App\Actions\Comms\Email;

use App\Actions\CRM\Appointment\SyncAppointmentStaffTask;
use App\Actions\OrgAction;
use App\Enums\CRM\Appointment\AppointmentStaffNoticeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Comms\DispatchedEmail;
use App\Models\CRM\Appointment;
use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The staff who get the task of an appointment are also emailed about it: when a customer books it,
 * when it is given to them, moved or cancelled. Each member of staff is one job, so an address that
 * fails does not hold back or repeat the others.
 */
class SendAppointmentEmailToStaff extends OrgAction
{
    public string $jobQueue = 'low-priority';

    public static function isEnabled(Shop $shop): bool
    {
        return (bool) Arr::get($shop->settings, 'appointments.notify_staff', true);
    }

    /**
     * @return Collection<int, User>
     */
    public static function staffToEmail(Appointment $appointment): Collection
    {
        return SyncAppointmentStaffTask::make()->staffToRemind($appointment)
            ->filter(fn (User $user) => filter_var($user->email, FILTER_VALIDATE_EMAIL))
            ->unique(fn (User $user) => strtolower($user->email))
            ->values();
    }

    public function handle(int $appointmentId, int $userId, string $notice, ?string $previousStartsAt = null): ?DispatchedEmail
    {
        $appointment = Appointment::with(['shop', 'appointmentType', 'user'])->find($appointmentId);
        $user        = User::find($userId);

        if (!$appointment || !$user || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $notice = AppointmentStaffNoticeEnum::from($notice);

        return SendNewAppointmentEmailToShop::make()->sendTo(
            $appointment,
            $user->email,
            fn () => $user,
            makeSubject: $notice === AppointmentStaffNoticeEnum::BOOKED
                ? null
                : fn () => $notice->subject($appointment->contact_name, SendAppointmentEmail::make()->localStart($appointment)->translatedFormat('j F Y, H:i')),
            makeIntroHtml: fn () => $this->introHtml($appointment, $notice, $previousStartsAt),
        );
    }

    private function introHtml(Appointment $appointment, AppointmentStaffNoticeEnum $notice, ?string $previousStartsAt): ?string
    {
        $appointmentEmail = SendAppointmentEmail::make();
        $start            = $appointmentEmail->localStart($appointment);
        $replacements     = [
            'name' => e($appointment->contact_name),
            'type' => e($appointment->appointmentType?->name ?? __('an appointment')),
            'date' => $start->translatedFormat('l j F Y'),
            'time' => $start->format('H:i'),
        ];

        return match ($notice) {
            AppointmentStaffNoticeEnum::BOOKED      => null,
            AppointmentStaffNoticeEnum::ASSIGNED    => $appointmentEmail->paragraph(__('The appointment of :name, :type on :date at :time, is now yours.', $replacements)),
            AppointmentStaffNoticeEnum::RESCHEDULED => $appointmentEmail->paragraph($previousStartsAt
                ? __('The appointment of :name, :type, moved from :previous to :date at :time.', [
                    ...$replacements,
                    'previous' => Carbon::parse($previousStartsAt)->setTimezone($start->timezone)->translatedFormat('l j F Y, H:i'),
                ])
                : __('The appointment of :name, :type, moved to :date at :time.', $replacements)),
            AppointmentStaffNoticeEnum::CANCELLED   => $appointmentEmail->paragraph(__(':type with :name on :date at :time is cancelled.', $replacements))
                .($appointment->state_reason ? $appointmentEmail->paragraph(e($appointment->state_reason)) : ''),
        };
    }
}
