<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 11:34:34 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\UI;

use App\Actions\Helpers\History\IndexHistories;
use App\Actions\InertiaAction;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\Inventory\WarehouseArea\UI\ShowWarehouseArea;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Enums\UI\LocationTabsEnum;
use App\Http\Resources\SysAdmin\HistoryResource;
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
                'navigation'                            => [
                    'previous' => $this->getPrevious($location, $request),
                    'next'     => $this->getNext($location, $request),
                ],
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-inventory'],
                            'title' => __('location')
                        ],
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

                LocationTabsEnum::SHOWCASE->value => $this->tab == LocationTabsEnum::SHOWCASE->value ?
                    fn () => GetLocationShowcase::run($location)
                    : Inertia::lazy(fn () => GetLocationShowcase::run($location)),

                LocationTabsEnum::HISTORY->value => $this->tab == LocationTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistories::run($location))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistories::run($location)))
            ]
        )->table(IndexHistories::make()->tableStructure());
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
                            'name'       => 'inventory.locations.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'inventory.locations.show',
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

    public function getPrevious(Location $location, ActionRequest $request): ?array
    {
        $previous=Location::where('code', '<', $location->code)->when(true, function ($query) use ($location, $request) {
            switch ($request->route()->getName()) {
                case 'inventory.warehouses.show.locations.show':
                    $query->where('locations.warehouse_id', $location->warehouse_id);
                    break;
                case 'inventory.warehouses.show.warehouse-areas.show.locations.show':
                case 'inventory.warehouse-areas.show.locations.show':
                    $query->where('locations.warehouse_area_id', $location->warehouse_area_id);
                    break;

            }
        })->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Location $location, ActionRequest $request): ?array
    {
        $next = Location::where('code', '>', $location->code)->when(true, function ($query) use ($location, $request) {
            switch ($request->route()->getName()) {
                case 'inventory.warehouses.show.locations.show':
                    $query->where('locations.warehouse_id', $location->warehouse_id);
                    break;
                case 'inventory.warehouses.show.warehouse-areas.show.locations.show':
                case 'inventory.warehouse-areas.show.locations.show':
                    $query->where('locations.warehouse_area_id', $location->warehouse_area_id);
                    break;

            }
        })->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Location $location, string $routeName): ?array
    {
        if(!$location) {
            return null;
        }
        return match ($routeName) {
            'inventory.locations.show'=> [
                'label'=> $location->code,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'location'  => $location->slug
                    ]

                ]
            ],
            'inventory.warehouse-areas.show.locations.show' => [
                'label'=> $location->code,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'warehouseArea' => $location->warehouseArea->slug,
                        'location'      => $location->slug
                    ]

                ]
            ],
            'inventory.warehouses.show.locations.show'=> [
                'label'=> $location->code,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'warehouse' => $location->warehouse->slug,
                        'location'  => $location->slug
                    ]

                ]
            ],
            'inventory.warehouses.show.warehouse-areas.show.locations.show' => [
                'label'=> $location->code,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'warehouse'     => $location->warehouse->slug,
                        'warehouseArea' => $location->warehouseArea->slug,
                        'location'      => $location->slug
                    ]

                ]
            ]
        };
    }

}
