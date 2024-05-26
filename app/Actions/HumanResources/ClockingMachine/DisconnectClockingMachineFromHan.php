<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 26 May 2024 15:43:56 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\ClockingMachine;

use App\Actions\Traits\WithActionUpdate;
use App\Enums\HumanResources\ClockingMachine\ClockingMachineStatusEnum;
use App\Models\HumanResources\ClockingMachine;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DisconnectClockingMachineFromHan
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
