<?php

namespace App\Actions\CRM\Appointment;

use App\Actions\Comms\Email\SendAppointmentEmailToStaff;
use App\Actions\CRM\Appointment\GoogleCalendar\SyncAppointmentGoogleCalendarEvent;
use Illuminate\Support\Arr;
use App\Actions\CRM\Appointment\Zoom\SyncAppointmentZoomMeeting;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Models\CRM\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateAppointment extends OrgAction
{
    use WithActionUpdate;
    use WithAppointmentRules;
    use WithAppointmentNotification;

    private Appointment $appointment;

    /**
     * @throws \Throwable
     */
    public function handle(Appointment $appointment, array $modelData): Appointment
    {
        $previousState    = $appointment->state;
        $previousStartsAt = $appointment->starts_at->copy();
        $previousUserId   = $appointment->user_id;
        $previousStaff    = SendAppointmentEmailToStaff::staffToEmail($appointment);

        $appointment = DB::transaction(function () use ($appointment, $modelData, $previousState, $previousStartsAt, $previousUserId, $previousStaff) {
            $modelData = $this->prepareAppointmentData($appointment->shop, $modelData, $appointment);

            $this->ensureAppointmentFits($modelData, $appointment);

            $appointment = $this->update($appointment, $modelData);

            if (!$appointment->starts_at->equalTo($previousStartsAt)) {
                $appointment->forceFill([
                    'reminder_sent_at' => RemindUpcomingAppointments::isInsideReminderWindow($appointment) ? now() : null,
                ])->saveQuietly();
            }

            $this->notifyVisitorAboutChange($appointment, $previousState, $previousStartsAt);
            $this->notifyStaffAboutChange($appointment, $previousState, $previousStartsAt, $previousUserId, $previousStaff);

            return $appointment;
        });

        SyncAppointmentStaffTask::run($appointment);
        SyncAppointmentGoogleCalendarEvent::dispatchFor($appointment);

        if ($appointment->appointmentType?->isZoomCall() || Arr::has($appointment->data, 'zoom')) {
            SyncAppointmentZoomMeeting::dispatch($appointment->id);
        }

        return $appointment;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo("crm.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        return [
            ...$this->appointmentRules(isUpdate: true, appointment: $this->appointment),
            'state'        => ['sometimes', Rule::enum(AppointmentStateEnum::class)],
            'state_reason' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            ...$this->appointmentPhoneMessages(),
            ...$this->appointmentEmailMessages(),
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->discardDialCodeOnlyPhone();
    }

    public function asController(Appointment $appointment, ActionRequest $request): Appointment
    {
        $this->appointment = $appointment;
        $this->initialisationFromShop($appointment->shop, $request);

        return $this->handle($appointment, $this->validatedData);
    }

    public function action(Appointment $appointment, array $modelData): Appointment
    {
        $this->asAction    = true;
        $this->appointment = $appointment;
        $this->initialisationFromShop($appointment->shop, $modelData);

        return $this->handle($appointment, $this->validatedData);
    }
}
