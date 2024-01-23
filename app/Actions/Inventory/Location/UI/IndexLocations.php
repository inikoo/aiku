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
use App\Actions\OrgAction;
use App\Actions\UI\Inventory\ShowInventoryDashboard;
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

class IndexLocations extends OrgAction
{
    private Warehouse|WarehouseArea|Organisation $parent;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("inventories.{$this->organisation->id}.edit");

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo("inventories.{$this->organisation->id}.edit")
            );
    }

    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);
        $this->parent = $organisation;
        return $this->handle(parent: $organisation);
    }

    public function inWarehouse(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request)->withTab(WarehouseTabsEnum::values());
        $this->parent = $warehouse;
        return $this->handle(parent: $warehouse);
    }


    public function inWarehouseArea(Organisation $organisation, WarehouseArea $warehouseArea, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request)->withTab(WarehouseAreaTabsEnum::values());
        $this->parent = $warehouseArea;
        return $this->handle(parent: $warehouseArea);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouseInWarehouseArea(Organisation $organisation, Warehouse $warehouse, WarehouseArea $warehouseArea, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request)->withTab(WarehouseAreaTabsEnum::values());
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
                                    'name'       => 'grp.org.warehouses.create',
                                    'parameters' => array_values($request->route()->originalParameters())
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
                                    'name'       => 'grp.org.warehouses.show.locations.create',
                                    'parameters' => array_values($request->route()->originalParameters())
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
                                    'name'       => 'grp.org.warehouses.show.warehouse-areas.show.locations.create',
                                    'parameters' => array_values($request->route()->originalParameters())
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
                            $request->route()->getName() == 'grp.org.warehouses.show.locations.index' or
                            $request->route()->getName() == 'grp.org.warehouses.show.warehouse-areas.show.locations.index'
                        )
                            ? [
                            'type'  => 'button',
                            'style' => 'create',
                            'label' => __('locations'),
                            'route' => match ($request->route()->getName()) {
                                'grp.org.warehouses.show.locations.index' => [
                                    'name'       => 'grp.org.warehouses.show.locations.create',
                                    'parameters' => array_values($request->route()->originalParameters())
                                ],
                                default => [
                                    'name'       => 'grp.org.warehouses.show.warehouse-areas.show.locations.create',
                                    'parameters' => array_values($request->route()->originalParameters())
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
            'grp.org.inventory.locations.index' =>
            array_merge(
                (new ShowInventoryDashboard())->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'grp.org.inventory.locations.index',
                        null
                    ]
                )
            ),
            'grp.org.warehouses.show.locations.index' =>
            array_merge(
                (new ShowWarehouse())->getBreadcrumbs($routeParameters['warehouse']),
                $headCrumb([
                    'name'       => 'grp.org.warehouses.show.locations.index',
                    'parameters' =>  [
                        $routeParameters['organisation']->slug,
                        $routeParameters['warehouse']->slug
                    ]
                ])
            ),
            'grp.org.inventory.warehouse-areas.show.locations.index' =>
            array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs(
                    'grp.org.inventory.warehouse-areas.show',
                    [
                        'warehouseArea' => $routeParameters['warehouseArea']
                    ]
                ),
                $headCrumb([
                    'name'       => 'grp.org.inventory.warehouse-areas.show.locations.index',
                    'parameters' =>
                        [
                            $routeParameters['warehouseArea']->slug
                        ]
                ])
            ),
            'grp.org.warehouses.show.warehouse-areas.show.locations.index' =>
            array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs(
                    'grp.org.warehouses.show.warehouse-areas.show',
                    [
                        'warehouse'     => $routeParameters['warehouse'],
                        'warehouseArea' => $routeParameters['warehouseArea']
                    ]
                ),
                $headCrumb([
                    'name'       => 'grp.org.warehouses.show.warehouse-areas.show.locations.index',
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
