<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 Jan 2024 01:18:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Layout;

use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetWarehouseNavigation
{
    use AsAction;

    public function handle(Warehouse $warehouse, User $user): array
    {
        $navigation = [];


        if ($user->hasPermissionTo("inventory.$warehouse->id.view")) {
            $navigation['warehouse'] = [
                'scope'   => 'warehouses',
                'label'   => __('warehouse'),
                'icon'    => ['fal', 'fa-inventory'],
                'route'   => [
                    'name'       => 'grp.org.warehouses.show',
                    'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                ],
                'topMenu' => [
                    'subSections' => [
                        [
                            'tooltip' => __('warehouses'),
                            'icon'    => ['fal', 'fa-warehouse-alt'],
                            'route'   => [
                                'name'       => 'grp.org.warehouses.show',
                                'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                            ],
                            'label'   => null
                        ],


                        [
                            'label'   => __('areas'),
                            'tooltip' => __('Warehouse Areas'),
                            'icon'    => ['fal', 'fa-map-signs'],
                            'route'   => [
                                'name'       => 'grp.org.warehouses.show.warehouse-areas.index',
                                'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                            ],
                        ],
                        [
                            'label'   => __('locations'),
                            'tooltip' => __('Locations'),
                            'icon'    => ['fal', 'fa-inventory'],
                            'route'   => [
                                'name'       => 'grp.org.warehouses.show.locations.index',
                                'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                            ],
                        ],
                        [
                            'label'   => __('fulfilment'),
                            'tooltip' => __('fulfilment'),
                            'icon'    => ['fal', 'fa-pallets'],
                            'route'   => [
                                'name'       => 'grp.org.warehouses.show.pallets.index',
                                'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                            ],
                        ],

                    ],


                ]
            ];
        }


        if ($user->hasPermissionTo("dispatching.$warehouse->id.view")) {
            $navigation['dispatch'] = [
                'label'   => __('Dispatch'),
                'icon'    => ['fal', 'fa-conveyor-belt-alt'],
                'route'   => 'grp.dispatch.hub',
                'topMenu' => [
                    'subSections' => []
                ]
            ];
        }


        if ($user->hasPermissionTo("fulfilment.$warehouse->id..view")) {
            $navigation['fulfilment'] = [
                'icon'  => ['fal', 'fa-pallet-alt'],
                'label' => __('Fulfilment'),
                'route' => [
                    'name'       => 'grp.org.warehouses.show.pallets.index',
                    'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                ],


                'topMenu' => [
                    'subSections' => [



                    ]


                ],
            ];
        }




        return $navigation;
    }
}
