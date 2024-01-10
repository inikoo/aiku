<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 11:34:32 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\UI;

use App\Actions\InertiaAction;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\Inventory\WarehouseArea\UI\ShowWarehouseArea;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Enums\UI\WarehouseAreaTabsEnum;
use App\Enums\UI\WarehouseTabsEnum;
use App\Http\Resources\Inventory\LocationResource;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexLocations extends InertiaAction
{
    private Warehouse|WarehouseArea|Organisation $parent;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('inventory.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('inventory.view')
            );
    }

    public function inOrganisation(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        $this->parent = app('currentTenant');
        return $this->handle(parent: app('currentTenant'));
    }

    public function inWarehouse(Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request)->withTab(WarehouseTabsEnum::values());
        $this->parent = $warehouse;
        return $this->handle(parent: $warehouse);
    }


    public function inWarehouseArea(WarehouseArea $warehouseArea, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request)->withTab(WarehouseAreaTabsEnum::values());
        $this->parent = $warehouseArea;
        return $this->handle(parent: $warehouseArea);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouseInWarehouseArea(Warehouse $warehouse, WarehouseArea $warehouseArea, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request)->withTab(WarehouseAreaTabsEnum::values());
        $this->parent = $warehouseArea;
        return $this->handle(parent: $warehouseArea);
    }


    /** @noinspection PhpUndefinedMethodInspection */
    public function handle(Warehouse|WarehouseArea|Organisation $parent, $prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('locations.code', 'ILIKE', "%$value%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder=QueryBuilder::for(Location::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }

        return $queryBuilder
            ->defaultSort('locations.code')
            ->select(
                [
                    'locations.id',
                    'locations.code',
                    'locations.slug',
                    'warehouses.slug as warehouse_slug',
                    'warehouse_areas.slug as warehouse_area_slug',
                    'warehouse_area_id'
                ]
            )
            ->leftJoin('location_stats', 'location_stats.location_id', 'locations.id')
            ->leftJoin('warehouses', 'locations.warehouse_id', 'warehouses.id')
            ->leftJoin('warehouse_areas', 'locations.warehouse_area_id', 'warehouse_areas.id')
            ->when($parent, function ($query) use ($parent) {
                switch (class_basename($parent)) {
                    case 'WarehouseArea':
                        $query->where('locations.warehouse_area_id', $parent->id);
                        break;
                    case 'Warehouse':
                        $query->where('locations.warehouse_id', $parent->id);
                        break;
                }
            })
            ->allowedSorts(['code'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }


    public function tableStructure(Warehouse|WarehouseArea|Organisation $parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Organisation' => [
                            'title'       => __("No locations found"),
                            'description' => $this->canEdit && $parent->inventoryStats->number_warehouses==0 ? __('Get started by creating a new shop. ✨')
                                : __("In fact, is no even created a warehouse yet 🤷🏽‍♂️"),
                            'count'       => $parent->inventoryStats->number_locations,
                            'action'      => $this->canEdit ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new location'),
                                'label'   => __('location'),
                                'route'   => [
                                    'name'       => 'grp.inventory.warehouses.create',
                                    'parameters' => array_values($this->originalParameters)
                                ]
                            ] : null
                        ],
                        'Warehouse' => [
                            'title'       => __("No locations found"),
                            'description' => $this->canEdit ? __('Get started by creating a new location. ✨')
                                : null,
                            'count'       => $parent->stats->number_locations,
                            'action'      => $this->canEdit ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new location'),
                                'label'   => __('location'),
                                'route'   => [
                                    'name'       => 'grp.inventory.warehouses.show.locations.create',
                                    'parameters' => array_values($this->originalParameters)
                                ]
                            ] : null
                        ],
                        'WarehouseArea' => [
                            'title'       => __("No locations found"),
                            'description' => $this->canEdit ? __('Get started by creating a new location. ✨')
                                : null,
                            'count'       => $parent->stats->number_locations,
                            'action'      => $this->canEdit ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new location'),
                                'label'   => __('location'),
                                'route'   => [
                                    'name'       => 'grp.inventory.warehouses.show.warehouse-areas.show.locations.create',
                                    'parameters' => array_values($this->originalParameters)
                                ]
                            ] : null
                        ],
                        default => null
                    }
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }




    public function jsonResponse(LengthAwarePaginator $locations): AnonymousResourceCollection
    {
        return LocationResource::collection($locations);
    }


    public function htmlResponse(LengthAwarePaginator $locations, ActionRequest $request): Response
    {
        $scope    =$this->parent;
        $container=null;
        if (class_basename($scope) == 'Warehouse') {
            $container = [
                'icon'    => ['fal', 'fa-warehouse'],
                'tooltip' => __('Warehouse'),
                'label'   => Str::possessive($scope->name)
            ];
        } elseif (class_basename($scope) == 'WarehouseArea') {
            $container = [
                'icon'    => ['fal', 'fa-map-signs'],
                'tooltip' => __('Warehouse Area'),
                'label'   => Str::possessive($scope->name)
            ];
        }
        return Inertia::render(
            'Inventory/Locations',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('locations'),
                'pageHead'    => [
                    'title'        => __('locations'),
                    'container'    => $container,
                    'iconRight'    => [
                        'icon'  => ['fal', 'fa-inventory'],
                        'title' => __('locations')
                    ],
                    'actions'=> [
                        $this->canEdit
                        && (
                            $this->routeName == 'grp.inventory.warehouses.show.locations.index' or
                            $this->routeName == 'grp.inventory.warehouses.show.warehouse-areas.show.locations.index'
                        )
                            ? [
                            'type'  => 'button',
                            'style' => 'create',
                            'label' => __('locations'),
                            'route' => match ($this->routeName) {
                                'grp.inventory.warehouses.show.locations.index' => [
                                    'name'       => 'grp.inventory.warehouses.show.locations.create',
                                    'parameters' => array_values($this->originalParameters)
                                ],
                                default => [
                                    'name'       => 'grp.inventory.warehouses.show.warehouse-areas.show.locations.create',
                                    'parameters' => array_values($this->originalParameters)
                                ]
                            }
                        ] : false
                    ]
                ],
                'data'        => LocationResource::collection($locations),

            ]
        )->table($this->tableStructure($this->parent));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('locations'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.inventory.locations.index' =>
            array_merge(
                (new InventoryDashboard())->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'grp.inventory.locations.index',
                        null
                    ]
                )
            ),
            'grp.inventory.warehouses.show.locations.index' =>
            array_merge(
                (new ShowWarehouse())->getBreadcrumbs($routeParameters['warehouse']),
                $headCrumb([
                    'name'       => 'grp.inventory.warehouses.show.locations.index',
                    'parameters' =>
                        [
                            $routeParameters['warehouse']->slug
                        ]
                ])
            ),
            'grp.inventory.warehouse-areas.show.locations.index' =>
            array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs(
                    'grp.inventory.warehouse-areas.show',
                    [
                        'warehouseArea' => $routeParameters['warehouseArea']
                    ]
                ),
                $headCrumb([
                    'name'       => 'grp.inventory.warehouse-areas.show.locations.index',
                    'parameters' =>
                        [
                            $routeParameters['warehouseArea']->slug
                        ]
                ])
            ),
            'grp.inventory.warehouses.show.warehouse-areas.show.locations.index' =>
            array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs(
                    'grp.inventory.warehouses.show.warehouse-areas.show',
                    [
                        'warehouse'     => $routeParameters['warehouse'],
                        'warehouseArea' => $routeParameters['warehouseArea']
                    ]
                ),
                $headCrumb([
                    'name'       => 'grp.inventory.warehouses.show.warehouse-areas.show.locations.index',
                    'parameters' =>
                        [
                            $routeParameters['warehouse']->slug,
                            $routeParameters['warehouseArea']->slug,

                        ]
                ])
            ),

            default => []
        };
    }
}
