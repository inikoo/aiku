<?php

namespace App\Actions\Iris\Appointment;

use App\Actions\CRM\AppointmentType\GetAppointmentTypeAvailableSlots;
use App\Actions\IrisAction;
use App\Models\CRM\Appointment;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowIrisAppointment extends IrisAction
{
    use WithIrisAppointmentAccess;

    /**
     * @return array{appointment: array<string, mixed>, free_slots: array<string, array<int, string>>}
     */
    public function handle(Appointment $appointment): array
    {
        $appointmentType = $appointment->appointmentType;
        $canReschedule   = $appointment->canBeChangedByVisitor() && $appointmentType && !$appointmentType->trashed() && $appointmentType->is_active;

        return [
            'appointment' => $this->visitorAppointment($appointment),
            'free_slots'  => $canReschedule ? GetAppointmentTypeAvailableSlots::run($appointmentType) : [],
        ];
    }

    public function asController(Appointment $appointment, string $token, ActionRequest $request): array
    {
        $this->initialisation($request);
        $this->ensureVisitorLink($appointment, $token);

        return $this->handle($appointment);
    }

    public function htmlResponse(array $data): Response
    {
        return Inertia::render('ManageAppointment', [
            'title'       => __('Your appointment'),
            'shop_name'   => $this->shop->name,
            'appointment' => $data['appointment'],
            'free_slots'  => (object) $data['free_slots'],
        ]);
    }
}
