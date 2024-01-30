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
            $navigation['inventory'] = [
                'label'   => __('inventory'),
                'icon'    => ['fal', 'fa-pallet-alt'],
                'route'   => [
                    'name'       => 'grp.org.warehouses.show.inventory.dashboard',
                    'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                ],
                'topMenux' => [
                    'subSections' => [
                        [
                            'icon'  => ['fal', 'fa-chart-network'],
                            'route' => [
                                'name'       => 'grp.org.inventory.dashboard',
                                'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                            ]
                        ],

                        [
                            'label' => __('SKUs'),
                            'icon'  => ['fal', 'fa-box'],
                            'route' => [
                                'name'       => 'grp.org.inventory.org-stocks.index',
                                'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                            ]
                        ],
                        [
                            'label'   => __('SKUs Families'),
                            'tooltip' => __('SKUs families'),
                            'icon'    => ['fal', 'fa-boxes-alt'],
                            'route'   => [
                                'name'       => 'grp.org.inventory.org-stock-families.index',
                                'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                            ]
                        ],

                    ],


                ]
            ];
        }



        if ($user->hasPermissionTo("inventory.$warehouse->id.view")) {
            $navigation['warehouse'] = [
                'root'    => 'grp.org.warehouses.show.infrastructure.dashboard',
                'label'   => __('locations'),
                'icon'    => ['fal', 'fa-inventory'],
                'route'   => [
                    'name'       => 'grp.org.warehouses.show.infrastructure.dashboard',
                    'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                ],
                'topMenu' => [
                    'subSections' => [
                        [
                            'tooltip' => __('warehouses'),
                            'icon'    => ['fal', 'fa-warehouse-alt'],
                            'route'   => [
                                'name'       => 'grp.org.warehouses.show.infrastructure.dashboard',
                                'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                            ],
                            'label'   => null
                        ],


                        [
                            'label'   => __('areas'),
                            'tooltip' => __('Warehouse Areas'),
                            'icon'    => ['fal', 'fa-map-signs'],
                            'route'   => [
                                'name'       => 'grp.org.warehouses.show.infrastructure.warehouse-areas.index',
                                'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                            ],
                        ],
                        [
                            'label'   => __('locations'),
                            'tooltip' => __('Locations'),
                            'icon'    => ['fal', 'fa-inventory'],
                            'route'   => [
                                'name'       => 'grp.org.warehouses.show.infrastructure.locations.index',
                                'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                            ],
                        ],


                    ],


                ]
            ];
        }


        // if ($user->hasPermissionTo("dispatching.$warehouse->id.view")) {
        //     $navigation['dispatch'] = [
        //         'label'   => __('Dispatch'),
        //         'icon'    => ['fal', 'fa-conveyor-belt-alt'],
        //         'route'   => 'grp.dispatch.hub',
        //         'topMenu' => [
        //             'subSections' => []
        //         ]
        //     ];
        // }


        if ($user->hasPermissionTo("fulfilment.$warehouse->id..view")) {
            $navigation['fulfilment'] = [
                'root'  => 'grp.org.warehouses.show.fulfilment.',
                'icon'  => ['fal', 'fa-hand-holding-box'],
                'label' => __('Fulfilment'),
                'route' => [
                    'name'       => 'grp.org.warehouses.show.fulfilment.dashboard',
                    'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                ],


                'topMenu' => [
                    'subSections' => [


                        [
                            'tooltip' => __('fulfilment'),
                            'icon'    => ['fal', 'fa-chart-network'],
                            'route'   => [
                                'name'       => 'grp.org.warehouses.show.fulfilment.dashboard',
                                'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                            ],
                        ],

                        [
                            'label'   => __('Pallets'),
                            'tooltip' => __('pallets'),
                            'icon'    => ['fal', 'fa-pallet'],
                            'route'   => [
                                'name'       => 'grp.org.warehouses.show.fulfilment.pallets.index',
                                'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                            ],
                        ],


                    ]


                ],
            ];
        }




        return $navigation;
    }
}
