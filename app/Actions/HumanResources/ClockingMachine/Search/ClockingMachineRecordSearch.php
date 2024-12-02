<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 13-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\HumanResources\ClockingMachine\Search;

use App\Models\HumanResources\ClockingMachine;
use Lorisleiva\Actions\Concerns\AsAction;

class ClockingMachineRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(ClockingMachine $clockingMachine): void
    {
        if ($clockingMachine->trashed()) {
            if ($clockingMachine->universalSearch) {
                $clockingMachine->universalSearch()->delete();
            }

            return;
        }

        $clockingMachine->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $clockingMachine->group_id,
                'organisation_id'   => $clockingMachine->organisation_id,
                'organisation_slug' => $clockingMachine->organisation->slug,
                'sections'          => ['hr'],
                'haystack_tier_1'   => $clockingMachine->name,
                'haystack_tier_2'   => $clockingMachine->workplace->name,
                'result'            => [
                    'route'      => [
                        'name'       => 'grp.org.hr.clocking_machines.show',
                        'parameters' => [
                            'organisation' => $clockingMachine->organisation->slug,
                            'clockingMachine'     => $clockingMachine->slug,
                        ]
                    ],
                    'code' => [
                        'label' => $clockingMachine->name,
                    ],
                    'icon'       => [
                        'icon' => 'fal fa-chess-clock'
                    ]
                ]
            ]
        );
    }


}
