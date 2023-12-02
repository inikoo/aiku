<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 16:34:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Inventory;

use App\Actions\UI\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Models\Auth\User;
use App\Models\Inventory\Warehouse;
use App\Models\Grouping\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @property Organisation $organisation
 * @property User $user
 */
class InventoryDashboard
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
                'name'      => __('warehouses areas'),
                'shortName' => __('areas'),
                'icon'      => ['fal', 'fa-map-signs'],
                'href'      => ['inventory.warehouses.show.warehouse-areas.index', $warehouse->slug],
                'index'     => [
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
                'name'      => __('warehouses areas'),
                'shortName' => __('areas'),
                'icon'      => ['fal', 'fa-map-signs'],
                'href'      => ['inventory.warehouse-areas.index'],
                'index'     => [
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
                'breadcrumbs'  => $this->getBreadcrumbs(),
                'title'        => __('inventory'),
                'pageHead'     => [
                    'title' => __('inventory'),
                ],
                'flatTreeMaps' => [
                    [
                        $warehousesNode,
                        $warehouseAreasNode,
                        $locationsNode
                    ],
                    [
                        [
                            'name'  => __('SKUs families'),
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
        return
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'inventory.dashboard'
                            ],
                            'label' => __('inventory'),
                        ]
                    ]
                ]
            );
    }
}
