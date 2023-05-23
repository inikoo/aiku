<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 08:39:50 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\WarehouseArea\UI;

use App\Actions\InertiaAction;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Enums\UI\WarehouseAreaTabsEnum;
use App\Http\Resources\Inventory\LocationResource;
use App\Http\Resources\Inventory\WarehouseAreaResource;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowWarehouseArea extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.warehouse-areas.edit');

        return $request->user()->hasPermissionTo("inventory.view");
    }

    public function inTenant(WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $this->initialisation($request)->withTab(WarehouseAreaTabsEnum::values());

        return $warehouseArea;
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Warehouse $warehouse, WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $this->initialisation($request)->withTab(WarehouseAreaTabsEnum::values());

        return $warehouseArea;
    }

    public function htmlResponse(WarehouseArea $warehouseArea, ActionRequest $request): Response
    {
        return Inertia::render(
            'Inventory/WarehouseArea',
            [
                'title'                                 => __('warehouse area'),
                'breadcrumbs'                           => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($warehouseArea, $request),
                    'next'     => $this->getNext($warehouseArea, $request),
                ],
                'pageHead'                              => [
                    'icon'  => 'fal fa-map-signs',
                    'title' => $warehouseArea->name,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                    'meta'  => [
                        [
                            'name'     => trans_choice('location|locations', $warehouseArea->stats->number_locations),
                            'number'   => $warehouseArea->stats->number_locations,
                            'href'     =>
                                match ($this->routeName) {
                                    'inventory.warehouses.show.warehouse-areas.show' => [
                                        'inventory.warehouses.show.warehouse-areas.show.locations.index',
                                        [$warehouseArea->warehouse->slug, $warehouseArea->slug]
                                    ],
                                    default => [
                                        'inventory.warehouse-areas.show.locations.index',
                                        $warehouseArea->slug
                                    ]
                                }


                            ,
                            'leftIcon' => [
                                'icon'    => 'fal fa-inventory',
                                'tooltip' => __('locations')
                            ]
                        ]
                    ]

                ],
                'tabs'                                  => [
                    'current'    => $this->tab,
                    'navigation' => WarehouseAreaTabsEnum::navigation()
                ],
                WarehouseAreaTabsEnum::LOCATIONS->value => $this->tab == WarehouseAreaTabsEnum::LOCATIONS->value ?
                    fn () => LocationResource::collection(IndexLocations::run($warehouseArea))
                    : Inertia::lazy(fn () => LocationResource::collection(IndexLocations::run($warehouseArea))),


            ]
        )->table(IndexLocations::make()->tableStructure());
    }


    public function jsonResponse(WarehouseArea $warehouseArea): WarehouseAreaResource
    {
        return new WarehouseAreaResource($warehouseArea);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (WarehouseArea $warehouseArea, array $routeParameters, $suffix) {
            return [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('warehouse areas')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $warehouseArea->code,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ];
        };

        return match ($routeName) {
            'inventory.warehouse-areas.show' => array_merge(
                (new InventoryDashboard())->getBreadcrumbs(),
                $headCrumb(
                    $routeParameters['warehouseArea'],
                    [
                        'index' => [
                            'name'       => 'inventory.warehouse-areas.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'inventory.warehouse-areas.show',
                            'parameters' => [
                                $routeParameters['warehouseArea']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            'inventory.warehouses.show.warehouse-areas.show' => array_merge(
                (new ShowWarehouse())->getBreadcrumbs($routeParameters['warehouse']),
                $headCrumb(
                    $routeParameters['warehouseArea'],
                    [
                        'index' => [
                            'name'       => 'inventory.warehouses.show.warehouse-areas.index',
                            'parameters' => [$routeParameters['warehouse']->slug]
                        ],
                        'model' => [
                            'name'       => 'inventory.warehouses.show.warehouse-areas.show',
                            'parameters' => [
                                $routeParameters['warehouse']->slug,
                                $routeParameters['warehouseArea']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }

    public function getPrevious(WarehouseArea $warehouseArea, ActionRequest $request): ?array
    {
        $previous = WarehouseArea::where('code', '<', $warehouseArea->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(WarehouseArea $warehouseArea, ActionRequest $request): ?array
    {
        $next = WarehouseArea::where('code', '>', $warehouseArea->code)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?WarehouseArea $warehouseArea, string $routeName): ?array
    {
        if (!$warehouseArea) {
            return null;
        }

        return match ($routeName) {
            'inventory.warehouses.show.warehouse-areas.show' => [
                'label' => $warehouseArea->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'warehouse'     => $warehouseArea->warehouse->slug,
                        'warehouseArea' => $warehouseArea->slug
                    ]

                ]
            ]
        };
    }

}
