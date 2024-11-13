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

    public function handle(ClockingMachine $clockingmachine): void
    {
        if ($clockingmachine->trashed()) {
            if ($clockingmachine->universalSearch) {
                $clockingmachine->universalSearch()->delete();
            }

            return;
        }

        $clockingmachine->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $clockingmachine->group_id,
                'organisation_id'   => $clockingmachine->organisation_id,
                'organisation_slug' => $clockingmachine->organisation->slug,
                'sections'          => ['hr'],
                'haystack_tier_1'   => trim($clockingmachine->slug.' '.$clockingmachine->name),
                'result'            => [
                    'route'      => [
                        'name'       => 'grp.org.hr.clocking_machines.show',
                        'parameters' => [
                            'organisation' => $clockingmachine->organisation->slug,
                            'clockingMachine'     => $clockingmachine->slug,
                        ]
                    ],
                    'code' => [
                        'label' => $clockingmachine->name,
                    ],
                    'icon'       => [
                        'icon' => 'fal fa-chess-clock'
                    ]
                ]
            ]
        );
    }


}
