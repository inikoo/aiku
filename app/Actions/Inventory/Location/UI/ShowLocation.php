<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 11:34:34 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\Inventory\WarehouseArea\UI\ShowWarehouseArea;
use App\Actions\OrgAction;
use App\Actions\UI\Inventory\ShowInventoryDashboard;
use App\Enums\UI\LocationTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowLocation extends OrgAction
{
    public function handle(Location $location): Location
    {
        return $location;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("inventories.{$this->organisation->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("inventories.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("inventories.{$this->organisation->id}.view");
    }

    public function inOrganisation(Organisation $organisation, Warehouse $warehouse, Location $location, ActionRequest $request): Location
    {
        $this->organisation = $organisation;
        $this->initialisation($organisation, $request);
        return $this->handle($location);
    }

    public function asController(Organisation $organisation, Location $location, ActionRequest $request): Location
    {
        $this->organisation = $organisation;
        $this->initialisation($organisation, $request);
        return $this->handle($location);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Organisation $organisation, Warehouse $warehouse, Location $location, ActionRequest $request): Location
    {
        $this->initialisation($organisation, $request);
        return $this->handle($location);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouseArea(Organisation $organisation, WarehouseArea $warehouseArea, Location $location, ActionRequest $request): Location
    {
        $this->initialisation($organisation, $request);
        return $this->handle($location);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouseInWarehouseArea(Organisation $organisation, Warehouse $warehouse, WarehouseArea $warehouseArea, Location $location, ActionRequest $request): Location
    {
        $this->initialisation($organisation, $request);
        return $this->handle($location);
    }

    public function htmlResponse(Location $location, ActionRequest $request): Response
    {

        return Inertia::render(
            'Org/Warehouse/Location',
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
                    'icon'    => [
                        'title' => __('locations'),
                        'icon'  => 'fal fa-inventory'
                    ],
                    'title'   => $location->slug,
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                        $this->canDelete ?
                            match ($request->route()->getName()) {
                                'grp.org.inventory.locations.show' => [
                                    'type'  => 'button',
                                    'style' => 'delete',
                                    'route' => [
                                        'name'       => 'grp.org.inventory.locations.remove',
                                        'parameters' => $request->route()->originalParameters()
                                    ],

                                ],
                                'grp.org.warehouses.show.infrastructure.locations.show' => [
                                    'type'  => 'button',
                                    'style' => 'delete',
                                    'route' => [
                                        'name'       => 'grp.org.warehouses.show.infrastructure.locations.remove',
                                        'parameters' => $request->route()->originalParameters()
                                    ],
                                ],
                                'grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.show' => [
                                    'type'  => 'button',
                                    'style' => 'delete',
                                    'route' => [
                                        'name'       => 'grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.remove',
                                        'parameters' => $request->route()->originalParameters()
                                    ]
                                ]
                            } : false
                    ]
                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => LocationTabsEnum::navigation()

                ],

                LocationTabsEnum::SHOWCASE->value => $this->tab == LocationTabsEnum::SHOWCASE->value ?
                    fn () => GetLocationShowcase::run($location)
                    : Inertia::lazy(fn () => GetLocationShowcase::run($location)),

                LocationTabsEnum::HISTORY->value => $this->tab == LocationTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($location))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($location)))
            ]
        )->table(IndexHistory::make()->tableStructure());
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
                            'label' => $location->slug,
                        ],

                    ],
                    'suffix'=> $suffix
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.inventory.locations.show' =>
            array_merge(
                ShowInventoryDashboard::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $routeParameters['location'],
                    [
                        'index' => [
                            'name'       => 'grp.org.inventory.locations.index',
                            'parameters' => [
                                $routeParameters['organisation']->slug,
                                $routeParameters['location']->slug,
                            ]
                        ],
                        'model' => [
                            'name'       => 'grp.org.inventory.locations.show',
                            'parameters' => [
                                $routeParameters['organisation']->slug,
                                $routeParameters['location']->slug
                            ]
                        ]
                    ],
                    $suffix
                ),
            ),
            'grp.org.warehouses.show.infrastructure.locations.show' => array_merge(
                (new ShowWarehouse())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $routeParameters['location'],
                    [
                        'index' => [
                            'name'       => 'grp.org.warehouses.show.infrastructure.locations.index',
                            'parameters' => [
                                $routeParameters['organisation']->slug,
                                $routeParameters['warehouse']->slug,
                            ]
                        ],
                        'model' => [
                            'name'       => 'grp.org.warehouses.show.infrastructure.locations.show',
                            'parameters' => [
                                $routeParameters['organisation']->slug,
                                $routeParameters['warehouse']->slug,
                                $routeParameters['location']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.inventory.warehouse-areas.show.locations.show' => array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs(
                    'grp.org.inventory.warehouse-areas.show',
                    [
                        'warehouseArea' => $routeParameters['warehouseArea']
                    ]
                ),
                $headCrumb(
                    $routeParameters['location'],
                    [
                        'index' => [
                            'name'       => 'grp.org.inventory.warehouse-areas.show.locations.index',
                            'parameters' => [
                                $routeParameters['warehouseArea']->slug,
                            ]
                        ],
                        'model' => [
                            'name'       => 'grp.org.inventory.warehouse-areas.show.locations.show',
                            'parameters' => [
                                $routeParameters['warehouseArea']->slug,
                                $routeParameters['location']->slug
                            ]
                        ]
                    ],
                    $suffix
                ),
            ),
            'grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.show' => array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs(
                    'grp.org.warehouses.show.infrastructure.warehouse-areas.show',
                    [
                        'warehouse'     => $routeParameters['warehouse'],
                        'warehouseArea' => $routeParameters['warehouseArea'],
                    ]
                ),
                $headCrumb(
                    $routeParameters['location'],
                    [
                        'index' => [
                            'name'       => 'grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.index',
                            'parameters' => [
                                $routeParameters['warehouse']->slug,
                                $routeParameters['warehouseArea']->slug,
                            ]
                        ],
                        'model' => [
                            'name'       => 'grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.show',
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
        $previous=Location::where('slug', '<', $location->slug)->when(true, function ($query) use ($location, $request) {
            switch ($request->route()->getName()) {
                case 'grp.org.warehouses.show.infrastructure.locations.show':
                    $query->where('locations.warehouse_id', $location->warehouse_id);
                    break;
                case 'grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.show':
                case 'grp.org.inventory.warehouse-areas.show.locations.show':
                    $query->where('locations.warehouse_area_id', $location->warehouse_area_id);
                    break;

            }
        })->orderBy('slug', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Location $location, ActionRequest $request): ?array
    {
        $next = Location::where('slug', '>', $location->slug)->when(true, function ($query) use ($location, $request) {
            switch ($request->route()->getName()) {
                case 'grp.org.warehouses.show.infrastructure.locations.show':
                    $query->where('locations.warehouse_id', $location->warehouse_id);
                    break;
                case 'grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.show':
                case 'grp.org.inventory.warehouse-areas.show.locations.show':
                    $query->where('locations.warehouse_area_id', $location->warehouse_area_id);
                    break;

            }
        })->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Location $location, string $routeName): ?array
    {
        if(!$location) {
            return null;
        }
        return match ($routeName) {
            'grp.org.inventory.locations.show'=> [
                'label'=> $location->slug,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'location'  => $location->slug
                    ]

                ]
            ],
            'grp.org.inventory.warehouse-areas.show.locations.show' => [
                'label'=> $location->slug,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'warehouseArea' => $location->warehouseArea->slug,
                        'location'      => $location->slug
                    ]

                ]
            ],
            'grp.org.warehouses.show.infrastructure.locations.show'=> [
                'label'=> $location->slug,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'organisation' => $location->organisation->slug,
                        'warehouse'    => $location->warehouse->slug,
                        'location'     => $location->slug
                    ]

                ]
            ],
            'inventory.warehouses.show.warehouse-areas.show.locations.show' => [
                'label'=> $location->slug,
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
