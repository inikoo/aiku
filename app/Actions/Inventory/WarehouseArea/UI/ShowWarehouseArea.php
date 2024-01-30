<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 08:39:50 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\WarehouseArea\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\OrgAction;
use App\Actions\UI\Inventory\ShowInventoryDashboard;
use App\Enums\UI\WarehouseAreaTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Inventory\LocationResource;
use App\Http\Resources\Inventory\WarehouseAreaResource;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowWarehouseArea extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("inventories.{$this->organisation->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("inventories.{$this->organisation->id}.edit");
        return $request->user()->hasPermissionTo("inventories.{$this->organisation->id}.view");
    }

    public function inOrganisation(Organisation $organisation, Warehouse $warehouse, WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $this->initialisation($organisation, $request)->withTab(WarehouseAreaTabsEnum::values());

        return $warehouseArea;
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Organisation $organisation, Warehouse $warehouse, WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $this->initialisation($organisation, $request)->withTab(WarehouseAreaTabsEnum::values());

        return $warehouseArea;
    }

    public function htmlResponse(WarehouseArea $warehouseArea, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Warehouse/WarehouseArea',
            [
                'title'       => __('warehouse area'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'navigation' => [
                    'previous' => $this->getPrevious($warehouseArea, $request),
                    'next'     => $this->getNext($warehouseArea, $request),
                ],
                'pageHead' => [
                    'icon' =>
                        [
                            'icon'  => ['fal', 'fa-map-signs'],
                            'title' => __('warehouse area')
                        ],
                    'title'   => $warehouseArea->name,
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                        $this->canDelete ? [
                            'type'  => 'button',
                            'style' => 'delete',
                            'route' => [
                                'name'       => 'grp.org.warehouses.show.warehouse-areas.remove',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]

                        ] : false
                    ],
                    /*'meta' => [
                        [
                            'name'   => trans_choice('location|locations', $warehouseArea->stats->number_locations),
                            'number' => $warehouseArea->stats->number_locations,
                            'href'   =>
                                match ($request->route()->getName()) {
                                    'grp.org.warehouses.show.warehouse-areas.show' => [
                                        'grp.org.warehouses.show.warehouse-areas.show.locations.index',
                                        [$warehouseArea->organisation->slug, $warehouseArea->warehouse->slug, $warehouseArea->slug]
                                    ],
                                    default => [
                                        'grp.org.warehouse-areas.show.locations.index',
                                        [
                                            $warehouseArea->organisation->slug,
                                            $warehouseArea->slug
                                        ]
                                    ]
                                },
                            'leftIcon' => [
                                'icon'    => 'fal fa-inventory',
                                'tooltip' => __('locations')
                            ]
                        ]
                    ]*/
                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => WarehouseAreaTabsEnum::navigation()
                ],
                WarehouseAreaTabsEnum::SHOWCASE->value => $this->tab == WarehouseAreaTabsEnum::SHOWCASE->value ?
                    fn () => GetWarehouseAreaShowcase::run($warehouseArea)
                    : Inertia::lazy(fn () => GetWarehouseAreaShowcase::run($warehouseArea)),

                WarehouseAreaTabsEnum::LOCATIONS->value => $this->tab == WarehouseAreaTabsEnum::LOCATIONS->value ?
                    fn () => LocationResource::collection(
                        IndexLocations::run(
                            parent: $warehouseArea,
                            prefix: 'locations'
                        )
                    )
                    : Inertia::lazy(fn () => LocationResource::collection(
                        IndexLocations::run(
                            parent: $warehouseArea,
                            prefix: 'locations'
                        )
                    )),

                WarehouseAreaTabsEnum::HISTORY->value => $this->tab == WarehouseAreaTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($warehouseArea))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($warehouseArea)))

            ]
        )->table(
            IndexLocations::make()->tableStructure(
                parent: $warehouseArea,
                /* modelOperations: [
                   'createLink' => $this->canEdit ? [
                       match ($request->route()->getName()) {
                           'grp.oms.warehouses.show.warehouse-areas.show' =>
                           [
                               'route' => [
                                   'name' => 'grp.oms.warehouses.show.warehouse-areas.show.locations.create',
                                   'parameters' => array_values([$warehouseArea->slug])
                               ]
                           ],
                           'grp.oms.warehouses.show' =>
                           [
                               'route' => [
                                   'name' => 'grp.oms.warehouses.show.locations.create',
                                   'parameters' => array_values([$warehouseArea->warehouse->slug])
                               ]
                           ],

                           default =>
                           [
                               'route' => [
                                   'name' => 'grp.oms.warehouse-areas.show.locations.create',
                                   'parameters' => array_values([$warehouseArea->slug])
                               ]
                           ],
                       },
                       'label' => __('location'),
                       'style' => 'create'
                   ] : false
                ], */
                prefix: WarehouseAreaTabsEnum::LOCATIONS->value
            )
        )->table(IndexHistory::make()->tableStructure());
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
                            'label' => $warehouseArea->slug,
                        ],
                    ],
                    'suffix' => $suffix,

                ],
            ];
        };

        return match ($routeName) {
            'grp.org.warehouse-areas.show' =>
            array_merge(
                (new ShowInventoryDashboard())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $routeParameters['warehouseArea'],
                    [
                        'index' => [
                            'name'       => 'grp.oms.warehouse-areas.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'grp.oms.warehouse-areas.show',
                            'parameters' => [
                                $routeParameters['warehouseArea']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.warehouses.show.warehouse-areas.show' =>
            array_merge(
                (new ShowWarehouse())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $routeParameters['warehouseArea'],
                    [
                        'index' => [
                            'name'       => 'grp.org.warehouses.show.warehouse-areas.index',
                            'parameters' => [
                                $routeParameters['organisation']->slug,
                                $routeParameters['warehouse']->slug
                            ]
                        ],
                        'model' => [
                            'name'       => 'grp.org.warehouses.show.warehouse-areas.show',
                            'parameters' => [
                                $routeParameters['organisation']->slug,
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
        $previous = WarehouseArea::where('code', '<', $warehouseArea->code)->when(true, function ($query) use ($warehouseArea, $request) {
            if ($request->route()->getName() == 'grp.org.warehouses.show.warehouse-areas.show') {
                $query->where('warehouse_id', $warehouseArea->warehouse_id);
            }
        })->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(WarehouseArea $warehouseArea, ActionRequest $request): ?array
    {
        $next = WarehouseArea::where('code', '>', $warehouseArea->code)->when(true, function ($query) use ($warehouseArea, $request) {
            if ($request->route()->getName() == 'grp.org.warehouses.show.warehouse-areas.show') {
                $query->where('warehouse_id', $warehouseArea->warehouse->id);
            }
        })->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?WarehouseArea $warehouseArea, string $routeName): ?array
    {
        if (!$warehouseArea) {
            return null;
        }

        return match ($routeName) {
            'grp.org.inventory.warehouse-areas.show' => [
                'label' => $warehouseArea->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'  => $warehouseArea->organisation->slug,
                        'warehouseArea' => $warehouseArea->slug
                    ]

                ]
            ],
            'grp.org.warehouses.show.warehouse-areas.show' => [
                'label' => $warehouseArea->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'  => $warehouseArea->organisation->slug,
                        'warehouse'     => $warehouseArea->warehouse->slug,
                        'warehouseArea' => $warehouseArea->slug
                    ]

                ]
            ],
            default => []
        };
    }

}
