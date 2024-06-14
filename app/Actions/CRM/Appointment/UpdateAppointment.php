<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Appointment;

use App\Actions\Traits\WithActionUpdate;
use App\Enums\CRM\Appointment\AppointmentEventEnum;
use App\Enums\CRM\Appointment\AppointmentTypeEnum;
use App\Models\CRM\Appointment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsCommand;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Throwable;

class UpdateAppointment
{
    use AsAction;
    use WithAttributes;
    use AsCommand;
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(Appointment $appointment, array $modelData): Model
    {
        return $this->update($appointment, $modelData);
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id'              => ['sometimes'],
            'name'                     => ['sometimes', 'string'],
            'schedule_at'              => ['sometimes'],
            'description'              => ['sometimes', 'string', 'max:255'],
            'type'                     => ['sometimes', Rule::enum(AppointmentTypeEnum::class)],
            'event'                    => ['sometimes', Rule::enum(AppointmentEventEnum::class)],
            'event_address'            => ['sometimes', 'string']
        ];
    }

    /**
     * @throws Throwable
     */
    public function asController(Appointment $appointment, ActionRequest $request): Model
    {
        $this->fillFromRequest($request);
        $request->validate();

        return $this->handle($appointment, $request->validated());
    }
}
