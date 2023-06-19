<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\ClockingMachine\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\HumanResources\ClockingMachine;
use Lorisleiva\Actions\Concerns\AsAction;

class ClockingMachineHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(ClockingMachine $clockingMachine): void
    {
        $clockingMachine->universalSearch()->create(
            [
                'section' => 'HumanResources',
                'route'   => json_encode([
                    'name'      => 'hr.clocking-machines.show',
                    'arguments' => [
                        $clockingMachine->slug
                    ]
                ]),
                'icon'           => 'fa-chess-clock',
                'primary_term'   => $clockingMachine->code,
                'secondary_term' => $clockingMachine->workplace_id
            ]
        );
    }

}
