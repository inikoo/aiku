<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 15:31:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Warehouse\UI;

use App\Actions\Helpers\History\IndexHistories;
use App\Actions\InertiaAction;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\WarehouseArea\UI\IndexWarehouseAreas;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Enums\UI\WarehouseTabsEnum;
use App\Http\Resources\Inventory\LocationResource;
use App\Http\Resources\Inventory\WarehouseAreaResource;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Http\Resources\SysAdmin\HistoryResource;
use App\Models\Inventory\Warehouse;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Warehouse $warehouse
 */
class ShowWarehouse extends InertiaAction
{
    public function handle(Warehouse $warehouse): Warehouse
    {
        return $warehouse;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->can('inventory.edit');
        $this->canDelete = $request->user()->can('inventory.edit');
        return $request->user()->hasPermissionTo("inventory.view");
    }

    public function asController(Warehouse $warehouse, ActionRequest $request): Warehouse
    {
        $this->initialisation($request)->withTab(WarehouseTabsEnum::values());

        return $this->handle($warehouse);
    }


    public function htmlResponse(Warehouse $warehouse, ActionRequest $request): Response
    {
        return Inertia::render(
            'Inventory/Warehouse',
            [
                'title'                            => __('warehouse'),
                'breadcrumbs'                      => $this->getBreadcrumbs($warehouse),
                'navigation'                       => [
                    'previous' => $this->getPrevious($warehouse, $request),
                    'next'     => $this->getNext($warehouse, $request),
                ],
                'pageHead'                         => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'warehouse'],
                            'title' => __('warehouse')
                        ],
                    'title'   => $warehouse->name,
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ] : false,
                        $this->canDelete ? [
                            'type'  => 'button',
                            'style' => 'delete',
                            'route' => [
                                'name'       => 'inventory.warehouses.remove',
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ] : false
                    ],
                    'meta' => [
                        [
                            'name'     => trans_choice('warehouse area|warehouse areas', $warehouse->stats->number_warehouse_areas),
                            'number'   => $warehouse->stats->number_warehouse_areas,
                            'href'     => [
                                'inventory.warehouses.show.warehouse-areas.index',
                                $warehouse->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-map-signs',
                                'tooltip' => __('warehouse areas')
                            ]
                        ],
                        [
                            'name'     => trans_choice('location|locations', $warehouse->stats->number_locations),
                            'number'   => $warehouse->stats->number_locations,
                            'href'     => [
                                'inventory.warehouses.show.locations.index',
                                $warehouse->slug
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
                    fn () => GetWarehouseShowcase::run($warehouse)
                    : Inertia::lazy(fn () => GetWarehouseShowcase::run($warehouse)),

                WarehouseTabsEnum::WAREHOUSE_AREAS->value => $this->tab == WarehouseTabsEnum::WAREHOUSE_AREAS->value
                    ?
                    fn () => WarehouseAreaResource::collection(
                        IndexWarehouseAreas::run(
                            parent: $warehouse,
                            prefix: 'warehouse_areas'
                        )
                    )
                    : Inertia::lazy(fn () => WarehouseAreaResource::collection(
                        IndexWarehouseAreas::run(
                            parent: $warehouse,
                            prefix: 'warehouse_areas'
                        )
                    )),

                WarehouseTabsEnum::LOCATIONS->value       => $this->tab == WarehouseTabsEnum::LOCATIONS->value ?
                    fn () => LocationResource::collection(
                        IndexLocations::run(
                            parent: $warehouse,
                            prefix: 'locations'
                        )
                    )
                    : Inertia::lazy(fn () => LocationResource::collection(
                        IndexLocations::run(
                            parent: $warehouse,
                            prefix: 'locations'
                        )
                    )),

                WarehouseTabsEnum::HISTORY->value => $this->tab == WarehouseTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistories::run($warehouse))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistories::run($warehouse)))

            ]
        )->table(
            IndexWarehouseAreas::make()->tableStructure(
                /*  modelOperations: [
                      'createLink' => $this->canEdit ? [
                          'route' => [
                              'name'       => 'inventory.warehouses.show.warehouse-areas.create',
                              'parameters' => array_values($this->originalParameters)
                          ],
                          'label' => __('area')
                      ] : false,
                  ],
                  prefix: 'warehouse_areas' */
            )
        )->table(
            IndexLocations::make()->tableStructure(
                modelOperations: [
                    'createLink' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'inventory.warehouses.show.locations.create',
                            'parameters' => array_values([$warehouse->slug])
                        ],
                        'label' => __('location'),
                        'style' => 'create'
                    ] : false
                ],
                prefix: 'locations'
            )
        )->table(IndexHistories::make()->tableStructure());
    }


    public function jsonResponse(Warehouse $warehouse): WarehouseResource
    {
        return new WarehouseResource($warehouse);
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
