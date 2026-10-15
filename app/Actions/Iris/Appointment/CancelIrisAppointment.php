<?php

namespace App\Actions\Iris\Appointment;

use App\Actions\CRM\Appointment\UpdateAppointment;
use App\Actions\IrisAction;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Models\CRM\Appointment;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class CancelIrisAppointment extends IrisAction
{
    use WithIrisAppointmentAccess;

    /**
     * @throws \Throwable
     */
    public function handle(Appointment $appointment, array $modelData): Appointment
    {
        $this->ensureVisitorCanChange($appointment);

        $reason = Arr::get($modelData, 'reason');

        return UpdateAppointment::make()->action($appointment, [
            'state'        => AppointmentStateEnum::CANCELLED->value,
            'state_reason' => $reason ? __('Cancelled by the visitor: :reason', ['reason' => $reason]) : __('Cancelled by the visitor'),
        ]);
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:500'],
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
