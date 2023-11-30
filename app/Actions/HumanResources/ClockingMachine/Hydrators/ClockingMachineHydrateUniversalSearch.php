<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\ClockingMachine\Hydrators;

use App\Models\HumanResources\ClockingMachine;
use Lorisleiva\Actions\Concerns\AsAction;

class ClockingMachineHydrateUniversalSearch
{
    use AsAction;


    public function handle(ClockingMachine $clockingMachine): void
    {
        $clockingMachine->universalSearch()->updateOrCreate(
            [],
            [
                'section'     => 'hr',
                'title'       => $clockingMachine->code,
                'description' => $clockingMachine->workplace->name
            ]
        );
    }

}
