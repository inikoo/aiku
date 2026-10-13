<?php

namespace App\Actions\Iris\Appointment;

use App\Actions\IrisAction;
use App\Actions\Retina\CRM\Appointment\RescheduleRetinaAppointment;
use App\Models\CRM\Appointment;
use Lorisleiva\Actions\ActionRequest;

class RescheduleIrisAppointment extends IrisAction
{
    use WithIrisAppointmentAccess;

    /**
     * @throws \Throwable
     */
    public function handle(Appointment $appointment, array $modelData): Appointment
    {
        $this->ensureVisitorCanChange($appointment);

        return RescheduleRetinaAppointment::make()->moveToFreeTime($appointment, $modelData['date'], $modelData['time']);
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
        ];
    }

    /**
     * @throws \Throwable
     */
    public function asController(Appointment $appointment, string $token, ActionRequest $request): Appointment
    {
        $this->initialisation($request);
        $this->ensureVisitorLink($appointment, $token);

        return $this->handle($appointment, $this->validatedData);
    }

    public function jsonResponse(Appointment $appointment): array
    {
        return $this->visitorAppointment($appointment);
    }
}
