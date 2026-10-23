<?php

namespace App\Actions\CRM\Appointment;

use App\Actions\Comms\Email\SendAppointmentEmail;
use App\Actions\Comms\Email\SendAppointmentEmailToStaff;
use App\Actions\Comms\Email\SendNewAppointmentEmailToShop;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Enums\CRM\Appointment\AppointmentStaffNoticeEnum;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Models\CRM\Appointment;
use App\Models\SysAdmin\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait WithAppointmentNotification
{
    protected function notifyVisitorAboutNewAppointment(Appointment $appointment): void
    {
        $code = match ($appointment->state) {
            AppointmentStateEnum::REQUESTED => OutboxCodeEnum::APPOINTMENT_REQUESTED,
            AppointmentStateEnum::ACCEPTED  => OutboxCodeEnum::APPOINTMENT_ACCEPTED,
            default                         => null,
        };

        $this->queueAppointmentEmail($appointment, $code);
    }

    protected function notifyShopAboutNewAppointment(Appointment $appointment): void
    {
        $shopAddress = SendNewAppointmentEmailToShop::recipientEmail($appointment->shop);

        if ($shopAddress) {
            DB::afterCommit(fn () => SendNewAppointmentEmailToShop::dispatch($appointment->id));
        }

        $staff = SendAppointmentEmailToStaff::staffToEmail($appointment)
            ->reject(fn (User $user) => $shopAddress && strcasecmp($user->email, $shopAddress) === 0);

        $this->queueStaffEmails($appointment, $staff, AppointmentStaffNoticeEnum::BOOKED);
    }

    /**
     * @param Collection<int, User> $previousStaff who had the appointment before the change
     */
    protected function notifyStaffAboutChange(Appointment $appointment, AppointmentStateEnum $previousState, Carbon $previousStartsAt, ?int $previousUserId, Collection $previousStaff): void
    {
        $state = $appointment->state;

        [$notice, $staff] = match (true) {
            $state === AppointmentStateEnum::CANCELLED && $previousState->holdsSlot()
            => [AppointmentStaffNoticeEnum::CANCELLED, $previousStaff],
            $state === AppointmentStateEnum::ACCEPTED && ($previousState !== AppointmentStateEnum::ACCEPTED || (int) $previousUserId !== (int) $appointment->user_id)
            => [AppointmentStaffNoticeEnum::ASSIGNED, SendAppointmentEmailToStaff::staffToEmail($appointment)],
            !$appointment->starts_at->equalTo($previousStartsAt) && $state->holdsSlot()
            => [AppointmentStaffNoticeEnum::RESCHEDULED, SendAppointmentEmailToStaff::staffToEmail($appointment)],
            default
            => [null, collect()],
        };

        if ($notice) {
            $this->queueStaffEmails($appointment, $staff, $notice, $notice === AppointmentStaffNoticeEnum::RESCHEDULED ? $previousStartsAt->toIso8601String() : null);
        }
    }

    /**
     * @param Collection<int, User> $staff
     */
    private function queueStaffEmails(Appointment $appointment, Collection $staff, AppointmentStaffNoticeEnum $notice, ?string $previousStartsAt = null): void
    {
        if (!SendAppointmentEmailToStaff::isEnabled($appointment->shop)) {
            return;
        }

        $actingUserId = auth('web')->id();

        foreach ($staff->reject(fn (User $user) => $user->id === $actingUserId) as $user) {
            DB::afterCommit(fn () => SendAppointmentEmailToStaff::dispatch($appointment->id, $user->id, $notice->value, $previousStartsAt));
        }
    }

    protected function notifyVisitorAboutChange(Appointment $appointment, AppointmentStateEnum $previousState, Carbon $previousStartsAt): void
    {
        $state = $appointment->state;
        $moved = !$appointment->starts_at->equalTo($previousStartsAt);

        $code = match (true) {
            $state === AppointmentStateEnum::DECLINED && $previousState !== AppointmentStateEnum::DECLINED       => OutboxCodeEnum::APPOINTMENT_DECLINED,
            $state === AppointmentStateEnum::CANCELLED && $previousState->holdsSlot()                             => OutboxCodeEnum::APPOINTMENT_CANCELLED,
            $moved && $state->holdsSlot()                                                                          => OutboxCodeEnum::APPOINTMENT_RESCHEDULED,
            $state === AppointmentStateEnum::ACCEPTED && $previousState === AppointmentStateEnum::REQUESTED       => OutboxCodeEnum::APPOINTMENT_ACCEPTED,
            default                                                                                                => null,
        };

        $this->queueAppointmentEmail($appointment, $code, $code === OutboxCodeEnum::APPOINTMENT_RESCHEDULED ? $previousStartsAt->toIso8601String() : null);
    }

    private function queueAppointmentEmail(Appointment $appointment, ?OutboxCodeEnum $code, ?string $previousStartsAt = null): void
    {
        if (!$code || !$appointment->email) {
            return;
        }

        DB::afterCommit(fn () => SendAppointmentEmail::dispatch($appointment->id, $code->value, $previousStartsAt));
    }
}
