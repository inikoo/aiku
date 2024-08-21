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


        if ($user->hasAnyPermission([
            "inventory.{$warehouse->organisation->id}.view",
            "stocks.{$warehouse->id}.view",
            "fulfilment.{$warehouse->id}.view",
        ])) {
            $navigation["inventory"] = [
                "root"    => "grp.org.warehouses.show.inventory.",
                "label"   => __("inventory"),
                "icon"    => ["fal", "fa-pallet-alt"],
                "route"   => [
                    "name"       => "grp.org.warehouses.show.inventory.dashboard",
                    "parameters" => [$warehouse->organisation->slug, $warehouse->slug],
                ],
                "topMenu" => [
                    "subSections" => [
                        [
                            "icon"  => ["fal", "fa-chart-network"],
                            'root'  => 'grp.org.warehouses.show.inventory.dashboard',
                            "route" => [
                                "name"       => "grp.org.warehouses.show.inventory.dashboard",
                                "parameters" => [$warehouse->organisation->slug, $warehouse->slug],
                            ],
                        ],
                        $user->hasAnyPermission([
                            "inventory.{$warehouse->organisation->id}.view",
                            "stocks.{$warehouse->id}.view",
                        ])
                            ? [
                            "label"   => __("SKUs Families"),
                            "tooltip" => __("SKUs families"),
                            "icon"    => ["fal", "fa-boxes-alt"],
                            'root'    => 'grp.org.warehouses.show.inventory.org_stock_families.',
                            "route"   => [
                                "name"       => "grp.org.warehouses.show.inventory.org_stock_families.index",
                                "parameters" => [$warehouse->organisation->slug, $warehouse->slug],
                            ],
                        ] : null,
                        $user->hasAnyPermission([
                            "inventory.{$warehouse->organisation->id}.view",
                            "stocks.{$warehouse->id}.view",
                        ])
                            ? [
                            "label" => __("SKUs"),
                            "icon"  => ["fal", "fa-box"],
                            'root'  => 'grp.org.warehouses.show.inventory.org_stocks.current_org_stocks.',
                            "route" => [
                                "name"       => "grp.org.warehouses.show.inventory.org_stocks.current_org_stocks.index",
                                "parameters" => [$warehouse->organisation->slug, $warehouse->slug],
                            ],
                        ] : null,


                        $user->hasPermissionTo("fulfilment.{$warehouse->id}.view", ) ?
                            [
                                "label"   => __("Stored pallets"),
                                "tooltip" => __("Fulfillment: Stored pallets"),
                                "icon"    => ["fal", "fa-pallet"],
                                "root"    => "grp.org.warehouses.show.inventory.pallets.",
                                "route"   => [
                                    "name"       => "grp.org.warehouses.show.inventory.pallets.current.index",
                                    "parameters" => [$warehouse->organisation->slug, $warehouse->slug],
                                ],
                            ] : null,

                        $user->hasPermissionTo("fulfilment.{$warehouse->id}.view", ) ?
                            [
                                "label"   => __("Stored items"),
                                "tooltip" => __("Fulfillment: Stored items"),
                                "icon"    => ["fal", "fa-narwhal"],
                                "root"    => "grp.org.warehouses.show.inventory.pallets.",
                                "route"   => [
                                    "name"       => "grp.org.warehouses.show.inventory.pallets.current.index",
                                    "parameters" => [$warehouse->organisation->slug, $warehouse->slug],
                                ],
                            ] : null,

                    ],
                ],
            ];
        }

        if ($user->hasPermissionTo("locations.$warehouse->id.view")) {
            $navigation["warehouse"] = [
                "root"    => "grp.org.warehouses.show.infrastructure.",
                "label"   => __("locations"),
                "icon"    => ["fal", "fa-inventory"],
                "route"   => [
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
                            "label"   => null,
                        ],
                        [
                            "root"    => "grp.org.warehouses.show.infrastructure.warehouse-areas.",
                            "label"   => __("areas"),
                            "tooltip" => __("Warehouse Areas"),
                            "icon"    => ["fal", "fa-map-signs"],
                            "route"   => [
                                "name"       =>
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

        if ($user->hasAnyPermission([
            "incoming.$warehouse->id.view",
            "fulfilment.$warehouse->id.view",
        ])) {
            $navigation["incoming"] = [
                "root"    => "grp.org.warehouses.show.incoming.",
                "label"   => __("Goods in"),
                "icon"    => ["fal", "fa-arrow-to-bottom"],
                "route"   => [
                    "name"       => "grp.org.warehouses.show.incoming.backlog",
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
                                "name"       => "grp.org.warehouses.show.incoming.backlog",
                                "parameters" => [
                                    $warehouse->organisation->slug,
                                    $warehouse->slug
                                ],
                            ]
                        ],
                        $user->hasPermissionTo("incoming.{$warehouse->id}.view", ) ?
                        [
                            'label' => __('stock deliveries'),
                            'icon'  => ['fal', 'fa-truck-container'],
                            'route' => [
                                "name"       => "grp.org.warehouses.show.incoming.stock_deliveries.index",
                                "parameters" => [
                                    $warehouse->organisation->slug,
                                    $warehouse->slug
                                ],
                            ]
                        ] : null,
                        $user->hasPermissionTo("fulfilment.{$warehouse->id}.view", ) ?
                        [
                            'label' => __('fulfilment deliveries'),
                            'icon'  => ['fal', 'fa-truck-couch'],
                            'route' => [
                                "name"       => "grp.org.warehouses.show.incoming.pallet_deliveries.index",
                                "parameters" => [
                                    $warehouse->organisation->slug,
                                    $warehouse->slug
                                ],
                            ]
                        ] : null,

                    ]
                ],
            ];
        }


        if ($user->hasAnyPermission(["dispatching.$warehouse->id.view", "fulfilment.$warehouse->id.view"])) {
            $navigation["dispatching"] = [
                "root"    => "grp.org.warehouses.show.dispatching.",
                "label"   => __("Goods out"),
                "icon"    => ["fal", "fa-arrow-from-left"],
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
                        $user->hasPermissionTo("dispatching.{$warehouse->id}.view", ) ?
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
                        ] : null,
                        $user->hasPermissionTo("fulfilment.{$warehouse->id}.view", ) ?
                        [
                            'label'   => __('Fulfilment Returns'),
                            'tooltip' => __('Fulfilment returns'),
                            'icon'    => ['fal', 'fa-sign-out'],
                            "root"    => "grp.org.warehouses.show.dispatching.pallet-returns.",
                            'route'   => [
                                'name'       => 'grp.org.warehouses.show.dispatching.pallet-returns.index',
                                'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                            ],
                        ] : null,
                    ]
                ],
            ];
        }


        return $navigation;
    }
}
