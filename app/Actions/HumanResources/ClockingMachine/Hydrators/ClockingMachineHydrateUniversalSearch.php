<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\ClockingMachine\Hydrators;

use App\Actions\WithRoutes;
use App\Actions\WithTenantJob;
use App\Models\HumanResources\ClockingMachine;
use Lorisleiva\Actions\Concerns\AsAction;

class ClockingMachineHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;
    use WithRoutes;

    public function handle(ClockingMachine $clockingMachine): void
    {
        $clockingMachine->universalSearch()->create(
            [
                'section' => 'HumanResources',
                'route' => $this->routes(),
                'icon' => 'fa-chess-clock',
                'primary_term'   => $clockingMachine->code,
                'secondary_term' => $clockingMachine->workplace_id
            ]
        );
    }

}
