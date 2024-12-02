<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 14-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Inventory\OrgStockFamily\Search;

use App\Models\Inventory\OrgStockFamily;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgStockFamilyRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(OrgStockFamily $orgStockFamily): void
    {
        #TODO: bug, inventory just only can search in the first warehouse of organisation
        # if organisation have many warehouse this will not work for the other except the first one
        # solution:
        # create new table 'warehouse_has_org_stock_family'
        # or make inventory outside of warehouse

        if ($orgStockFamily->trashed()) {
            $orgStockFamily->universalSearch()->delete();

            return;
        }

        $warehouse = $orgStockFamily->organisation->warehouses->first();
        $orgStockFamily->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $orgStockFamily->group_id,
                'organisation_id'   => $orgStockFamily->organisation_id,
                'organisation_slug' => $orgStockFamily->organisation->slug,
                'warehouse_id'      => $warehouse->id,
                'warehouse_slug'    => $warehouse->slug,
                'sections'          => ['inventory'],
                'haystack_tier_1'   => trim($orgStockFamily->code.' '.$orgStockFamily->name),
                'keyword'           => $orgStockFamily->code,
                'result'            => [
                    'route'     => [
                        'name'          => 'grp.org.warehouses.show.inventory.org_stock_families.show',
                        'parameters'    => [
                            $orgStockFamily->organisation->slug,
                            $warehouse->slug,
                            $orgStockFamily->slug
                        ]
                    ],
                    'description'      => [
                        'label' => $orgStockFamily->name,
                    ],
                    'code' => [
                        'label' => $orgStockFamily->code,
                    ],
                    'icon'      => [
                        'icon' => 'fal fa-boxes-alt',
                    ],
                    'meta'      => [
                        [
                            'label' => $orgStockFamily->state,
                            'tooltip' => __('State')
                        ],
                        [
                            'type'   => 'number',
                            'label'  => __('SKUs') . ': ',
                            'number' => (int) $orgStockFamily->stats->number_current_org_stocks
                        ],

                    ],
                ]
            ]
        );
    }

}
