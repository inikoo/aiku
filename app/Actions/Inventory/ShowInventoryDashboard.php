<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 14 Sept 2022 15:12:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory;

use App\Actions\UI\WithInertia;
use App\Models\Central\Tenant;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @property Tenant $tenant
 * @property User $user
 */
class ShowInventoryDashboard
{
    use AsAction;
    use WithInertia;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.view");
    }


    public function asController(ActionRequest $request): void
    {
        $this->user   = $request->user();
        $this->tenant = app('currentTenant');
    }


    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        if ($this->tenant->inventoryStats->number_warehouses == 1) {
            $warehouse          = Warehouse::first();
            $warehousesNode     = [
                'name' => __('warehouse'),
                'icon' => ['fal', 'fa-warehouse'],
                'href' => ['inventory.warehouses.show', $warehouse->slug],

            ];
            $warehouseAreasNode = [
                'name'  => __('warehouses areas'),
                'icon'  => ['fal', 'fa-map-signs'],
                'href'  => ['inventory.warehouses.show.warehouse_areas.index', $warehouse->slug],
                'index' => [
                    'number' => $this->tenant->inventoryStats->number_warehouse_areas
                ]
            ];
            $locationsNode      = [
                'name'  => __('locations'),
                'icon'  => ['fal', 'fa-inventory'],
                'href'  => ['inventory.warehouses.show.locations.index', $warehouse->slug],
                'index' => [
                    'number' => $this->tenant->inventoryStats->number_locations
                ]

            ];
        } else {
            $warehousesNode     = [
                'name'  => __('warehouses'),
                'icon'  => ['fal', 'fa-warehouse'],
                'href'  => ['inventory.warehouses.index'],
                'index' => [
                    'number' => $this->tenant->inventoryStats->number_warehouses
                ]
            ];
            $warehouseAreasNode = [
                'name'  => __('warehouses areas'),
                'icon'  => ['fal', 'fa-map-signs'],
                'href'  => ['inventory.warehouse_areas.index'],
                'index' => [
                    'number' => $this->tenant->inventoryStats->number_warehouse_areas
                ]
            ];
            $locationsNode      = [
                'name'  => __('locations'),
                'icon'  => ['fal', 'fa-inventory'],
                'href'  => ['inventory.locations.index'],
                'index' => [
                    'number' => $this->tenant->inventoryStats->number_locations
                ]

            ];
        }


        return Inertia::render(
            'Inventory/InventoryDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('inventory'),
                'pageHead'    => [
                    'title' => __('inventory'),
                ],
                'treeMaps'    => [
                    [
                        $warehousesNode,
                        $warehouseAreasNode,
                        $locationsNode
                    ],
                    [
                        [
                            'name'  => __('families'),
                            'icon'  => ['fal', 'fa-boxes-alt'],
                            'href'  => ['inventory.stock-families.index'],
                            'index' => [
                                'number' => $this->tenant->inventoryStats->number_stock_families
                            ]

                        ],
                        [
                            'name'  => 'SKUs',
                            'icon'  => ['fal', 'fa-box'],
                            'href'  => ['inventory.stocks.index'],
                            'index' => [
                                'number' => $this->tenant->inventoryStats->number_stocks
                            ]

                        ]
                    ]
                ]

            ]
        );
    }


    public function getBreadcrumbs(): array
    {
        return [
            'inventory.dashboard' => [
                'route' => 'inventory.dashboard',
                'name'  => __('inventory'),
            ]
        ];
    }
}
