<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 15:31:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Warehouse\UI;

use App\Actions\InertiaAction;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\WarehouseArea\UI\IndexWarehouseAreas;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Enums\UI\WarehouseTabsEnum;
use App\Http\Resources\Inventory\LocationResource;
use App\Http\Resources\Inventory\WarehouseAreaResource;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Models\Inventory\Warehouse;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Warehouse $warehouse
 */
class ShowWarehouse extends InertiaAction
{
    private ActionRequest $request;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.warehouses.edit');

        return $request->user()->hasPermissionTo("inventory.view");
    }

    public function asController(Warehouse $warehouse, ActionRequest $request): void
    {
        $this->initialisation($request)->withTab(WarehouseTabsEnum::values());
        $this->warehouse = $warehouse;
        $this->request   = $request;
    }


    public function htmlResponse(): Response
    {
        return Inertia::render(
            'Inventory/Warehouse',
            [
                'title'                            => __('warehouse'),
                'breadcrumbs'                      => $this->getBreadcrumbs($this->warehouse),
                'navigation'                       => [
                    'previous' => $this->getPrevious($this->warehouse, $this->request),
                    'next'     => $this->getNext($this->warehouse, $this->request),
                ],
                'pageHead'                         => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'warehouse'],
                            'title' => __('warehouse')
                        ],
                    'title' => $this->warehouse->name,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,


                    'meta' => [
                        [
                            'name'     => trans_choice('warehouse area|warehouse areas', $this->warehouse->stats->number_warehouse_areas),
                            'number'   => $this->warehouse->stats->number_warehouse_areas,
                            'href'     => [
                                'inventory.warehouses.show.warehouse-areas.index',
                                $this->warehouse->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-map-signs',
                                'tooltip' => __('warehouse areas')
                            ]
                        ],
                        [
                            'name'     => trans_choice('location|locations', $this->warehouse->stats->number_locations),
                            'number'   => $this->warehouse->stats->number_locations,
                            'href'     => [
                                'inventory.warehouses.show.locations.index',
                                $this->warehouse->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-inventory',
                                'tooltip' => __('locations')
                            ]
                        ]
                    ]

                ],
                'tabs'                             => [

                    'current'    => $this->tab,
                    'navigation' => WarehouseTabsEnum::navigation(),


                ],
                WarehouseTabsEnum::SHOWCASE->value => $this->tab == WarehouseTabsEnum::SHOWCASE->value ?
                    fn () => GetWarehouseShowcase::run($this->warehouse)
                    : Inertia::lazy(fn () => GetWarehouseShowcase::run($this->warehouse)),

                WarehouseTabsEnum::LOCATIONS->value       => $this->tab == WarehouseTabsEnum::LOCATIONS->value ?
                    fn () => LocationResource::collection(IndexLocations::run($this->warehouse))
                    : Inertia::lazy(fn () => LocationResource::collection(IndexLocations::run($this->warehouse))),
                WarehouseTabsEnum::WAREHOUSE_AREAS->value => $this->tab == WarehouseTabsEnum::WAREHOUSE_AREAS->value
                    ?
                    fn () => WarehouseAreaResource::collection(IndexWarehouseAreas::run($this->warehouse))
                    : Inertia::lazy(fn () => WarehouseAreaResource::collection(IndexWarehouseAreas::run($this->warehouse))),

            ]
        )->table(
            IndexLocations::make()->tableStructure(
                [
                    'createLink' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'inventory.warehouses.show.locations.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label' => __('location')
                    ] : false,
                ]
            ),
        )->table(
            IndexWarehouseAreas::make()->tableStructure(
                [
                    'createLink' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'inventory.warehouses.show.warehouse-areas.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label' => __('area')
                    ] : false,
                ]
            )
        );
    }


    public function jsonResponse(): WarehouseResource
    {
        return new WarehouseResource($this->warehouse);
    }

    public function getBreadcrumbs(Warehouse $warehouse, $suffix = null): array
    {
        return array_merge(
            (new InventoryDashboard())->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'inventory.warehouses.index',
                            ],
                            'label' => __('warehouse'),
                            'icon'  => 'fal fa-bars'
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'inventory.warehouses.show',
                                'parameters' => [$warehouse->slug]
                            ],
                            'label' => $warehouse->code,
                            'icon'  => 'fal fa-bars'
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(Warehouse $warehouse, ActionRequest $request): ?array
    {
        $previous = Warehouse::where('code', '<', $warehouse->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Warehouse $warehouse, ActionRequest $request): ?array
    {
        $next = Warehouse::where('code', '>', $warehouse->code)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Warehouse $warehouse, string $routeName): ?array
    {
        if (!$warehouse) {
            return null;
        }

        return match ($routeName) {
            'inventory.warehouses.show' => [
                'label' => $warehouse->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'warehouse' => $warehouse->slug
                    ]

                ]
            ]
        };
    }
}
