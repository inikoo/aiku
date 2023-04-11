<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 11:34:34 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\UI;

use App\Actions\InertiaAction;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\Inventory\WarehouseArea\UI\ShowWarehouseArea;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Enums\UI\LocationTabsEnum;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Location $location
 */
class ShowLocation extends InertiaAction
{
    public function handle(Location $location): Location
    {
        return $location;
    }
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.locations.edit');
        return $request->user()->hasPermissionTo("inventory.view");
    }


    public function inTenant(Location $location, ActionRequest $request): Location
    {
        $this->initialisation($request)->withTab(LocationTabsEnum::values());
        return $this->handle($location);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Warehouse $warehouse, Location $location, ActionRequest $request): Location
    {
        $this->initialisation($request)->withTab(LocationTabsEnum::values());
        return $this->handle($location);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouseArea(WarehouseArea $warehouseArea, Location $location, ActionRequest $request): Location
    {
        $this->initialisation($request)->withTab(LocationTabsEnum::values());
        return $this->handle($location);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouseInWarehouseArea(Warehouse $warehouse, WarehouseArea $warehouseArea, Location $location, ActionRequest $request): Location
    {
        $this->initialisation($request)->withTab(LocationTabsEnum::values());
        return $this->handle($location);
    }

    public function htmlResponse(Location $location, ActionRequest $request): Response
    {
        return Inertia::render(
            'Inventory/Location',
            [
                'title'       => __('location'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'pageHead'    => [
                    'icon'  => 'fal fa-inventory',
                    'title' => $location->code,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,

                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => LocationTabsEnum::navigation()

                ],
            ]
        );
    }


    public function jsonResponse(Location $location): JsonResource
    {
        return new JsonResource($location);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (Location $location, array $routeParameters, string $suffix) {
            return [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('locations')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $location->code,
                        ],

                    ],
                    'suffix'=> $suffix
                ],
            ];
        };

        return match ($routeName) {
            'inventory.locations.show' =>
            array_merge(
                InventoryDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    $routeParameters['location'],
                    [
                        'index' => [
                            'name'       => 'inventory.location.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'inventory.location.show',
                            'parameters' => [$routeParameters['location']->slug]
                        ]
                    ],
                    $suffix
                ),
            ),
            'inventory.warehouses.show.locations.show' => array_merge(
                (new ShowWarehouse())->getBreadcrumbs($routeParameters['warehouse']),
                $headCrumb(
                    $routeParameters['location'],
                    [
                        'index' => [
                            'name'       => 'inventory.warehouses.show.locations.index',
                            'parameters' => [
                                $routeParameters['warehouse']->slug,
                            ]
                        ],
                        'model' => [
                            'name'       => 'inventory.warehouses.show.locations.show',
                            'parameters' => [
                                $routeParameters['warehouse']->slug,
                                $routeParameters['location']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            'inventory.warehouse-areas.show.locations.show' => array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs(
                    'inventory.warehouse-areas.show',
                    [
                       'warehouseArea' => $routeParameters['warehouseArea']
                    ]
                ),
                $headCrumb(
                    $routeParameters['location'],
                    [
                        'index' => [
                            'name'       => 'inventory.warehouse-areas.show.locations.index',
                            'parameters' => [
                                $routeParameters['warehouseArea']->slug,
                            ]
                        ],
                        'model' => [
                            'name'       => 'inventory.warehouse-areas.show.locations.show',
                            'parameters' => [
                                $routeParameters['warehouseArea']->slug,
                                $routeParameters['location']->slug
                            ]
                        ]
                    ],
                    $suffix
                ),
            ),
            'inventory.warehouses.show.warehouse-areas.show.locations.show' => array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs(
                    'inventory.warehouses.show.warehouse-areas.show',
                    [
                      'warehouse'     => $routeParameters['warehouse'],
                      'warehouseArea' => $routeParameters['warehouseArea'],
                    ]
                ),
                $headCrumb(
                    $routeParameters['location'],
                    [
                        'index' => [
                            'name'       => 'inventory.warehouses.show.warehouse-areas.show.locations.index',
                            'parameters' => [
                                $routeParameters['warehouse']->slug,
                                $routeParameters['warehouseArea']->slug,
                            ]
                        ],
                        'model' => [
                            'name'       => 'inventory.warehouses.show.warehouse-areas.show.locations.show',
                            'parameters' => [
                                $routeParameters['warehouse']->slug,
                                $routeParameters['warehouseArea']->slug,
                                $routeParameters['location']->slug
                            ]
                        ]
                    ],
                    $suffix
                ),
            ),

            default => []
        };
    }
}
