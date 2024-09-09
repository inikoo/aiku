<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Aug 2024 21:53:05 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Warehouse\Search;

use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\Concerns\AsAction;

class WarehouseRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';


    public function handle(Warehouse $warehouse): void
    {
        if ($warehouse->trashed()) {
            $warehouse->universalSearch()->delete();
            return;
        }

        $warehouse->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $warehouse->group_id,
                'organisation_id'   => $warehouse->organisation_id,
                'organisation_slug' => $warehouse->organisation->slug,
                'sections'          => ['inventory'],
                'haystack_tier_1'   => trim($warehouse->name.' '.$warehouse->code),
                'keyword'           => $warehouse->code,
                'result'            => [
                    'route'     => [
                        'name'          => 'grp.org.warehouses.show.infrastructure.dashboard',
                        'parameters'    => [
                            $warehouse->organisation->slug,
                            $warehouse->slug,
                        ]
                    ],
                    'container'     => [
                        'key'     => 'address',
                        'label'   => $warehouse->location,
                    ],
                    'title'     => $warehouse->name,
                    'icon'      => [
                        'icon' => 'fal fa-warehouse',
                    ],
                    'meta'      => [
                        [
                            'key'   => 'state',
                            'label' => $warehouse->state
                        ],
                        [
                            'key'       => 'created_date',
                            'type'      => 'date',
                            'label'     => $warehouse->created_at,
                            'tooltip'   => 'Created at'
                        ],
                    ],
                ]
            ]
        );
    }

}
