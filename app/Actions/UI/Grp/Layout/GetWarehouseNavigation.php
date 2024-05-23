<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Feb 2024 07:12:46 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Grp\Layout;

use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetWarehouseNavigation
{
    use AsAction;

    public function handle(Warehouse $warehouse, User $user): array
    {
        $navigation = [];


        if ($user->hasPermissionTo("dispatching.$warehouse->id.view")) {
            $navigation["dispatching"] = [
                "root"    => "grp.org.warehouses.show.dispatching.",
                "label"   => __("dispatching"),
                "icon"    => ["fal", "fa-conveyor-belt-alt"],
                "route"   => [
                    "name"       => "grp.org.warehouses.show.dispatching.backlog",
                    "parameters" => [
                        $warehouse->organisation->slug,
                        $warehouse->slug
                    ],
                ],
                "topMenu" => [
                    'subSections' => [
                        [
                            'icon'  => ['fal', 'fa-tasks-alt'],
                            'route' => [
                                "name"       => "grp.org.warehouses.show.dispatching.backlog",
                                "parameters" => [
                                    $warehouse->organisation->slug,
                                    $warehouse->slug
                                ],
                            ]
                        ],
                        [
                            'label' => __('delivery notes'),
                            'icon'  => ['fal', 'fa-truck'],
                            'route' => [
                                "name"       => "grp.org.warehouses.show.dispatching.delivery-notes",
                                "parameters" => [
                                    $warehouse->organisation->slug,
                                    $warehouse->slug
                                ],
                            ]
                        ],
                    ]
                ],
            ];
        }


        if ($user->hasPermissionTo("inventory.$warehouse->id.view")) {


            $navigation["warehouse"] = [
                "root"  => "grp.org.warehouses.show.infrastructure.",
                "label" => __("locations"),
                "icon"  => ["fal", "fa-inventory"],
                "route" => [
                    "name"       => "grp.org.warehouses.show.infrastructure.dashboard",
                    "parameters" => [$warehouse->organisation->slug, $warehouse->slug],
                ],
                "topMenu" => [
                    "subSections" => [
                        [
                            "root"    => "grp.org.warehouses.show.infrastructure.dashboard",
                            "tooltip" => __("warehouses"),
                            "icon"    => ["fal", "fa-warehouse-alt"],
                            "route"   => [
                                "name"       => "grp.org.warehouses.show.infrastructure.dashboard",
                                "parameters" => [$warehouse->organisation->slug, $warehouse->slug],
                            ],
                            "label" => null,
                        ],
                        [
                            "root"    => "grp.org.warehouses.show.infrastructure.warehouse-areas.",
                            "label"   => __("areas"),
                            "tooltip" => __("Warehouse Areas"),
                            "icon"    => ["fal", "fa-map-signs"],
                            "route"   => [
                                "name" =>
                                    "grp.org.warehouses.show.infrastructure.warehouse-areas.index",
                                "parameters" => [$warehouse->organisation->slug, $warehouse->slug],
                            ],
                        ],
                        [
                            "root"    => "grp.org.warehouses.show.infrastructure.locations.",
                            "label"   => __("locations"),
                            "tooltip" => __("Locations"),
                            "icon"    => ["fal", "fa-inventory"],
                            "route"   => [
                                "name"       => "grp.org.warehouses.show.infrastructure.locations.index",
                                "parameters" => [$warehouse->organisation->slug, $warehouse->slug],
                            ],
                        ],
                    ],
                ],
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

        if ($user->hasPermissionTo("fulfilment.$warehouse->id.view")) {
            $navigation["fulfilment"] = [
                "root"  => "grp.org.warehouses.show.fulfilment.",
                "icon"  => ["fal", "fa-hand-holding-box"],
                "label" => __("Fulfilment"),
                "route" => [
                    "name"       => "grp.org.warehouses.show.fulfilment.dashboard",
                    "parameters" => [$warehouse->organisation->slug, $warehouse->slug],
                ],
                "topMenu" => [
                    "subSections" => [
                        [
                            "tooltip" => __("fulfilment"),
                            "icon"    => ["fal", "fa-chart-network"],
                            "root"    => "grp.org.warehouses.show.fulfilment.dashboard",
                            "route"   => [
                                "name"       => "grp.org.warehouses.show.fulfilment.dashboard",
                                "parameters" => [$warehouse->organisation->slug, $warehouse->slug],
                            ],
                        ],
                        [
                            "label"   => __("Locations"),
                            "tooltip" => __("Locations allowed for fulfilment"),
                            "icon"    => ["fal", "fa-inventory"],
                            "root"    => "grp.org.warehouses.show.fulfilment.locations.",
                            "route"   => [
                                "name"       => "grp.org.warehouses.show.fulfilment.locations.index",
                                "parameters" => [$warehouse->organisation->slug, $warehouse->slug],
                            ],
                        ],
                        [
                            "label"   => __("Pallets"),
                            "tooltip" => __("pallets"),
                            "icon"    => ["fal", "fa-pallet"],
                            "root"    => "grp.org.warehouses.show.fulfilment.pallets.",
                            "route"   => [
                                "name"       => "grp.org.warehouses.show.fulfilment.pallets.index",
                                "parameters" => [$warehouse->organisation->slug, $warehouse->slug],
                            ],
                        ],
                        [
                            'label'   => __('Deliveries'),
                            'tooltip' => __('deliveries'),
                            'icon'    => ['fal', 'fa-truck-couch'],
                            "root"    => "grp.org.warehouses.show.fulfilment.pallet-deliveries.",
                            'route'   => [
                                'name'       => 'grp.org.warehouses.show.fulfilment.pallet-deliveries.index',
                                'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                            ],
                        ],
                        [
                            'label'   => __('Returns'),
                            'tooltip' => __('returns'),
                            'icon'    => ['fal', 'fa-sign-out'],
                            "root"    => "grp.org.warehouses.show.fulfilment.pallet-returns.",
                            'route'   => [
                                'name'       => 'grp.org.warehouses.show.fulfilment.pallet-returns.index',
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
