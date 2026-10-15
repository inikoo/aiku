<?php

namespace App\Actions\Retina\CRM\Appointment;

use App\Actions\CRM\Appointment\UpdateAppointment;
use App\Actions\RetinaAction;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Models\CRM\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class CancelRetinaAppointment extends RetinaAction
{
    use WithRetinaAppointment;

    /**
     * @throws \Throwable
     */
    public function handle(Appointment $appointment, array $modelData): Appointment
    {
        $this->ensureCustomerCanChange($appointment);

        $reason = Arr::get($modelData, 'reason');

        return UpdateAppointment::make()->action($appointment, [
            'state'        => AppointmentStateEnum::CANCELLED->value,
            'state_reason' => $reason ? __('Cancelled by the customer: :reason', ['reason' => $reason]) : __('Cancelled by the customer'),
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
    public function asController(Appointment $appointment, ActionRequest $request): Appointment
    {
        $this->initialisation($request);
        $this->ensureAppointmentOfCustomer($appointment, $this->customer);

        return $this->handle($appointment, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
