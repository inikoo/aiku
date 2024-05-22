<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:36:36 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User\UI;

use App\Actions\Traits\WithActionUpdate;
use App\Enums\HumanResources\ClockingMachine\ClockingMachineStatusEnum;
use App\Models\HumanResources\ClockingMachine;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteClockingMachineApiToken
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(ClockingMachine $clockingMachine)
    {
        $this->update($clockingMachine, [
            'device_name' => null,
            'device_uuid' => null,
            'status'      => ClockingMachineStatusEnum::DISCONNECTED->value,
        ]);

        return $clockingMachine->currentAccessToken()->delete();
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->user());
    }
}
