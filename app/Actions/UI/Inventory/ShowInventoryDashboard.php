<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 16:34:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Inventory;

use App\Actions\OrgAction;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Models\SysAdmin\Organisation;
use App\Models\Inventory\Warehouse;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowInventoryDashboard extends OrgAction
{
    use AsAction;
    use WithInertia;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventories.{$this->organisation->slug}.view");
    }


    public function asController(Organisation $organisation, ActionRequest $request): ActionRequest
    {
        $this->initialisation($organisation, $request);

        return $request;
    }


    public function htmlResponse(ActionRequest $request): Response
    {
        $routeParameters = $request->route()->originalParameters();



        if ($this->organisation->inventoryStats->number_warehouses == 1) {
            $warehouse          = Warehouse::first();
            $warehousesNode     = [
                'name' => __('warehouse'),
                'icon' => ['fal', 'fa-warehouse'],
                'href' => [
                    'name'       => 'grp.org.warehouses.show',
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'warehouse'    => $warehouse->slug
                    ]
                ],

            ];
            $warehouseAreasNode = [
                'name'      => __('warehouses areas'),
                'shortName' => __('areas'),
                'icon'      => ['fal', 'fa-map-signs'],
                'href'      => [
                    'name'       => 'grp.org.warehouses.show.warehouse-areas.index',
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'warehouse'    => $warehouse->slug
                    ]
                ],
                'index'     => [
                    'number' => $this->organisation->inventoryStats->number_warehouse_areas
                ]
            ];
            $locationsNode      = [
                'name'  => __('locations'),
                'icon'  => ['fal', 'fa-inventory'],
                'href'  => [
                    'name'       => 'grp.org.warehouses.show.locations.index',
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'warehouse'    => $warehouse->slug
                    ]
                ],
                'index' => [
                    'number' => $this->organisation->inventoryStats->number_locations
                ]

            ];
        } else {
            $warehousesNode     = [
                'name'  => __('warehouses'),
                'icon'  => ['fal', 'fa-warehouse'],
                'href'  => [
                    'name'       => 'grp.org.warehouses.index',
                    'parameters' => $routeParameters
                ],
                'index' => [
                    'number' => $this->organisation->inventoryStats->number_warehouses
                ]
            ];
            $warehouseAreasNode = [
                'name'      => __('warehouses areas'),
                'shortName' => __('areas'),
                'icon'      => ['fal', 'fa-map-signs'],
                'href'      => [
                    'name'       => 'grp.org.inventory.warehouse-areas.index',
                    'parameters' => $routeParameters
                ],
                'index'     => [
                    'number' => $this->organisation->inventoryStats->number_warehouse_areas
                ]
            ];
            $locationsNode      = [
                'name'  => __('locations'),
                'icon'  => ['fal', 'fa-inventory'],
                'href'  => [
                    'name'       => 'grp.org.inventory.locations.index',
                    'parameters' => $routeParameters
                ],
                'index' => [
                    'number' => $this->organisation->inventoryStats->number_locations
                ]

            ];
        }


        return Inertia::render(
            'Inventory/InventoryDashboard',
            [
                'breadcrumbs'  => $this->getBreadcrumbs($request->route()->originalParameters()),
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
                            'href'  => [
                                'name'       => 'grp.org.inventory.stock-families.index',
                                'parameters' => $routeParameters
                            ],
                            'index' => [
                                'number' => $this->organisation->inventoryStats->number_stock_families
                            ]

                        ],
                        [
                            'name'  => 'SKUs',
                            'icon'  => ['fal', 'fa-box'],
                            'href'  => [
                                'name'       => 'grp.org.inventory.stocks.index',
                                'parameters' => $routeParameters
                            ],
                            'index' => [
                                'number' => $this->organisation->inventoryStats->number_stocks
                            ]

                        ]
                    ]
                ]

            ]
        );
    }


    public function getBreadcrumbs(array $routeParameters): array
    {
        return
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.inventory.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('inventory'),
                        ]
                    ]
                ]
            );
    }
}
