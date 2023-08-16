<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\UI;

use App\Actions\InertiaAction;
use App\Enums\UI\LocationTabsEnum;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditLocation extends InertiaAction
{
    public function handle(Location $location): Location
    {
        return $location;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.locations.edit');
        return $request->user()->hasPermissionTo("inventory.warehouses.view");
    }

    public function inTenant(Location $location, ActionRequest $request): Location
    {
        $this->initialisation($request);

        return $this->handle($location);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Warehouse $warehouse, Location $location, ActionRequest $request): Location
    {
        $this->initialisation($request);

        return $this->handle($location);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouseArea(WarehouseArea $warehouseArea, Location $location, ActionRequest $request): Location
    {
        $this->initialisation($request);

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
            'EditModel',
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
                'pageHead' => [
                    'title'    => $location->code,
                    'icon'     => [
                        'title' => __('locations'),
                        'icon'  => 'fal fa-inventory'
                    ],
                    'actions'  => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ]
                    ]
                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('id'),
                            'fields' => [
                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => $location->code
                                ],
                            ],
                            [
                                'title'  => __('capacity'),
                                'icon'   => 'fa-light fa-phone',
                                'fields' => [
                                    'max_weight' => [
                                        'type'  => 'input',
                                        'label' => __('max weight (kg)'),
                                        'value' => '',
                                    ],
                                    'max_volume' => [
                                        'type'  => 'input',
                                        'label' => __('max volume (m³)'),
                                        'value' => '',
                                    ],
                                ]
                            ],
                        ]
                    ],

                    'args' => [
                        'updateRoute' => [
                            'name'       => 'models.location.update',
                            'parameters' => $location->slug

                        ],
                    ]
                ]
            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowLocation::make()->getBreadcrumbs(
            routeName: preg_replace('/edit$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '(' . __('editing') . ')'
        );
    }

    public function getPrevious(Location $location, ActionRequest $request): ?array
    {
        $previous=Location::where('slug', '<', $location->slug)->when(true, function ($query) use ($location, $request) {
            switch ($request->route()->getName()) {
                case 'inventory.warehouses.show.locations.edit':
                    $query->where('locations.warehouse_id', $location->warehouse_id);
                    break;
                case 'inventory.warehouses.show.warehouse-areas.show.locations.edit':
                case 'inventory.warehouse-areas.show.locations.show':
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
                case 'inventory.warehouses.show.locations.edit':
                    $query->where('locations.warehouse_id', $location->warehouse_id);
                    break;
                case 'inventory.warehouses.show.warehouse-areas.show.locations.edit':
                case 'inventory.warehouse-areas.show.locations.show':
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
            'inventory.locations.edit'=> [
                'label'=> $location->slug,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'location'  => $location->slug
                    ]

                ]
            ],
            'inventory.warehouse-areas.show.locations.edit' => [
                'label'=> $location->slug,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'warehouseArea' => $location->warehouseArea->slug,
                        'location'      => $location->slug
                    ]

                ]
            ],
            'inventory.warehouses.show.locations.edit'=> [
                'label'=> $location->slug,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'warehouse' => $location->warehouse->slug,
                        'location'  => $location->slug
                    ]

                ]
            ],
            'inventory.warehouses.show.warehouse-areas.show.locations.edit' => [
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
