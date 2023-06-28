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
use App\Http\Resources\Inventory\WarehouseAreaResource;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\Tenancy\Tenant;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexWarehouseAreas extends InertiaAction
{
    /** @noinspection PhpUndefinedMethodInspection */
    public function handle(Warehouse|Tenant $parent, $prefix = null): LengthAwarePaginator
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
                    'warehouse_areas.code as code',
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

    public function tableStructure(?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    [
                        'title'       => __('no warehouses area'),
                        'description' => $this->canEdit ? __('Get started by creating a new warehouse area.') : null,
                        'count'       => app('currentTenant')->inventoryStats->number_warehouse_areas,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new warehouse area'),
                            'label'   => __('warehouse area'),
                            'route'   => [
                                'name'       => 'inventory.warehouse-areas.create',
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ] : null
                    ]
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_locations', label: __('locations'), canBeHidden: false, sortable: true)
                ->defaultSort('code');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.warehouse-areas.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('inventory.view')
            );
    }


    public function inTenant(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(app('currentTenant'));
    }

    public function inWarehouse(Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle($warehouse);
    }


    public function jsonResponse(LengthAwarePaginator $warehouseAreas): AnonymousResourceCollection
    {
        return WarehouseAreaResource::collection($warehouseAreas);
    }


    public function htmlResponse(LengthAwarePaginator $warehouseAreas, ActionRequest $request): Response
    {
        return Inertia::render(
            'Inventory/WarehouseAreas',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('warehouse areas'),
                'pageHead'    => [
                    'title'   => __('warehouse areas'),
                    'create'  => $this->canEdit && $this->routeName == 'inventory.warehouses.show.warehouse-areas.index' ? [
                        'route'     => [
                            'name'       => 'inventory.warehouses.show.warehouse-areas.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'withMulti' => [
                            'route' => [
                                'name'       => 'inventory.warehouses.show.warehouse-areas.create-multi',
                                'parameters' => array_values($this->originalParameters)
                            ],
                        ],
                        'label'     => __('warehouse area')
                    ] : false,
                    'actions' => [
                        $this->canEdit && $this->routeName == 'inventory.warehouses.show.warehouse-areas.index' ? [
                            'type'    => 'buttonGroup',
                            'buttons' => [
                                [
                                    'style' => 'create',
                                    'icon'  => ['far', 'fa-border-all'],
                                    'label' => '',
                                    'route' => [
                                        'name'       => 'inventory.warehouses.show.warehouse-areas.create-multi',
                                        'parameters' => array_values($this->originalParameters)
                                    ],
                                ],
                                [
                                    'style' => 'create',
                                    'label' => __('warehouse area'),
                                    'route' => [
                                        'name'       => 'inventory.warehouses.show.warehouse-areas.create',
                                        'parameters' => array_values($this->originalParameters)
                                    ],
                                ]
                            ]
                        ] : false
                    ]
                ],
                'data'        => WarehouseAreaResource::collection($warehouseAreas)
            ]
        )->table($this->tableStructure());
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
            'inventory.warehouse-areas.index' =>
            array_merge(
                (new InventoryDashboard())->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'inventory.warehouse-areas.index',
                        null
                    ]
                )
            ),
            'inventory.warehouses.show.warehouse-areas.index',
            =>
            array_merge(
                (new ShowWarehouse())->getBreadcrumbs(
                    $routeParameters['warehouse']
                ),
                $headCrumb([
                    'name'       => 'inventory.warehouses.show.warehouse-areas.index',
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
