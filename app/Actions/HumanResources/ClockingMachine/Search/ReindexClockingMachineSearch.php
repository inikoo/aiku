<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 13-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\HumanResources\ClockingMachine\Search;

use App\Actions\HydrateModel;
use App\Models\HumanResources\ClockingMachine;
use Illuminate\Support\Collection;

class ReindexClockingMachineSearch extends HydrateModel
{
    public string $commandSignature = 'search:clocking_machines {organisations?*} {--s|slugs=}';


    public function handle(ClockingMachine $clockingMachine): void
    {
        ClockingMachineRecordSearch::run($clockingMachine);
    }


    protected function getModel(string $slug): ClockingMachine
    {
        return ClockingMachine::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return ClockingMachine::withTrashed()->get();
    }
}
