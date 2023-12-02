<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 08:39:46 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\WarehouseArea\UI;

use App\Actions\InertiaAction;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Enums\UI\WarehouseTabsEnum;
use App\Http\Resources\Inventory\WarehouseAreaResource;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\Grouping\Organisation;
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

class IndexWarehouseAreas extends InertiaAction
{
    private Warehouse|Organisation $parent;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('inventory.warehouses.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('inventory.view')
            );
    }


    public function inTenant(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        $this->parent = app('currentTenant');
        return $this->handle(app('currentTenant'));
    }

    public function inWarehouse(Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request)->withTab(WarehouseTabsEnum::values());
        $this->parent = $warehouse;
        return $this->handle($warehouse);
    }

    /** @noinspection PhpUndefinedMethodInspection */
    public function handle(Warehouse|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('warehouse_areas.name', 'ILIKE', "%$value%")
                    ->orWhere('warehouse_areas.code', 'ILIKE', "%$value%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(WarehouseArea::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }

        return $queryBuilder
            ->defaultSort('warehouse_areas.code')
            ->select(
                [
                    'warehouse_areas.code',
                    'warehouse_areas.id',
                    'warehouse_areas.name',
                    'number_locations',
                    'warehouses.slug as warehouse_slug',
                    'warehouse_areas.slug'
                ]
            )
            ->leftJoin('warehouse_area_stats', 'warehouse_area_stats.warehouse_area_id', 'warehouse_areas.id')
            ->leftJoin('warehouses', 'warehouse_areas.warehouse_id', 'warehouses.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Warehouse') {
                    $query->where('warehouse_areas.warehouse_id', $parent->id);
                }
            })
            ->allowedSorts(['code', 'name', 'number_locations'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if ($prefix) {
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
                            'title'       => __("No warehouse areas found"),
                            'description' => $this->canEdit && $parent->stats->number_warehouses == 0 ? __('Get started by creating a warehouse area. ✨')
                                : __("In fact, is no even create a warehouse yet 🤷🏽‍♂️"),
                            'count'       => $parent->stats->number_warehouse_areas,
                            'action'      => $this->canEdit && $parent->stats->number_warehouses == 0 ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new warehouse'),
                                'label'   => __('warehouse'),
                                'route'   => [
                                    'name'       => 'grp.oms.warehouses.create',
                                    'parameters' => array_values($this->originalParameters)
                                ]
                            ] : null
                        ],
                        'Warehouse' => [
                            'title'       => __("No warehouse areas found"),
                            'description' => $this->canEdit ? __('Get started by creating a new warehouse area. ✨')
                                : null,
                            'count'       => $parent->stats->number_warehouse_areas,
                            'action'      => $this->canEdit ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new warehouse area'),
                                'label'   => __('warehouse area'),
                                'route'   => [
                                    'name'       => 'grp.oms.warehouses.show.warehouse-areas.create',
                                    'parameters' => array_values($this->originalParameters)
                                ]
                            ] : null
                        ],
                        default => null
                    }
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_locations', label: __('locations'), canBeHidden: false, sortable: true)
                ->defaultSort('code');
        };
    }




    public function jsonResponse(LengthAwarePaginator $warehouseAreas): AnonymousResourceCollection
    {
        return WarehouseAreaResource::collection($warehouseAreas);
    }


    public function htmlResponse(LengthAwarePaginator $warehouseAreas, ActionRequest $request): Response
    {
        $scope    =$this->parent;
        $container=null;
        if (class_basename($scope) == 'Warehouse') {
            $container = [
                'icon'    => ['fal', 'fa-warehouse'],
                'tooltip' => __('Warehouse'),
                'label'   => Str::possessive($scope->name)
            ];
        }
        return Inertia::render(
            'Inventory/WarehouseAreas',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('warehouse areas'),
                'pageHead'    => [
                    'title'        => __('warehouse areas'),
                    'container'    => $container,
                    'iconRight'    => [
                        'icon'  => ['fal', 'fa-map-signs'],
                        'title' => __('warehouse areas')
                    ],
                    'actions' => [
                        $this->canEdit && $this->routeName == 'grp.oms.warehouses.show.warehouse-areas.index' ? [
                            'type'    => 'buttonGroup',
                            'buttons' => [
                                [
                                    'style' => 'create',
                                    'icon'  => ['far', 'fa-border-all'],
                                    'label' => '',
                                    'route' => [
                                        'name'       => 'grp.oms.warehouses.show.warehouse-areas.create-multi',
                                        'parameters' => $request->route()->originalParameters()
                                    ],
                                ],
                                [
                                    'style' => 'create',
                                    'label' => __('warehouse area'),
                                    'route' => [
                                        'name'       => 'grp.oms.warehouses.show.warehouse-areas.create',
                                        'parameters' => $request->route()->originalParameters()
                                    ],
                                ]
                            ]
                        ] : false
                    ]
                ],
                'data'        => WarehouseAreaResource::collection($warehouseAreas)
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
                        'label' => __('warehouse areas'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.oms.warehouse-areas.index' =>
            array_merge(
                (new InventoryDashboard())->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'grp.oms.warehouse-areas.index',
                        null
                    ]
                )
            ),
            'grp.inventory.warehouses.show.warehouse-areas.index',
            =>
            array_merge(
                ShowWarehouse::make()->getBreadcrumbs($routeParameters['warehouse']),
                $headCrumb([
                    'name'       => 'grp.inventory.warehouses.show.warehouse-areas.index',
                    'parameters' =>
                        [
                            $routeParameters['warehouse']->slug
                        ]
                ])
            ),
            default => []
        };
    }
}
