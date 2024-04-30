<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:36:28 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\ClockingMachine;

use App\Actions\HumanResources\ClockingMachine\Hydrators\ClockingMachineHydrateClockings;
use App\Actions\HydrateModel;
use App\Models\HumanResources\ClockingMachine;
use Illuminate\Support\Collection;

class HydrateClockingMachine extends HydrateModel
{
    public string $commandSignature = 'hydrate:clocking-machine {organisations?*} {--i|id=}';


    public function handle(ClockingMachine $clockingMachine): void
    {
        ClockingMachineHydrateClockings::run($clockingMachine);
    }


    protected function getModel(string $slug): ClockingMachine
    {
        return ClockingMachine::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return ClockingMachine::get();
    }
}
