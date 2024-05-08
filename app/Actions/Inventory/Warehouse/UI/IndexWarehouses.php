<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 15:30:55 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Warehouse\UI;

use App\Actions\OrgAction;
use App\Actions\UI\Inventory\ShowInventoryDashboard;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Http\Resources\Inventory\WarehousesResource;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexWarehouses extends OrgAction
{
    private Organisation $parent;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('org-supervisor.'.$this->organisation->id);

        return $request->user()->hasAnyPermission(
            [
                'org-supervisor.'.$this->organisation->id,
                'warehouses-view.'.$this->organisation->id]
        );
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle($organisation);
    }

    public function handle(Organisation $organisation, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('warehouses.name', $value)
                    ->orWhereStartWith('warehouses.code', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Warehouse::class);

        $queryBuilder->where('organisation_id', $organisation->id);


        return $queryBuilder
            ->defaultSort('warehouses.code')
            ->select([
                'warehouses.code as code',
                'warehouses.id',
                'warehouses.name',
                'warehouse_stats.number_warehouse_areas',
                'warehouse_stats.number_locations',
                'warehouses.slug as slug'
            ])
            ->leftJoin('warehouse_stats', 'warehouse_stats.warehouse_id', 'warehouses.id')
            ->allowedSorts(['code', 'name', 'number_warehouse_areas', 'number_locations'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation $parent, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title'       => __('no warehouses'),
                        'description' => $this->canEdit ? __('Get started by creating a new warehouse.') : null,
                        'count'       => $parent->inventoryStats->number_warehouses,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new warehouse'),
                            'label'   => __('warehouse'),
                            'route'   => [
                                'name'       => 'grp.org.warehouses.create',
                                'parameters' => $parent->slug
                            ]
                        ] : null
                    ]
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_warehouse_areas', label: __('warehouse areas'), canBeHidden: false, sortable: true)
                ->column(key: 'number_locations', label: __('locations'), canBeHidden: false, sortable: true)
                ->defaultSort('code');
        };
    }


    public function jsonResponse(LengthAwarePaginator $warehouses): AnonymousResourceCollection
    {
        return WarehousesResource::collection($warehouses);
    }


    public function htmlResponse(LengthAwarePaginator $warehouses, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Warehouse/Warehouses',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => __('warehouses'),
                'pageHead'    => [
                    'title'   => __('warehouses'),
                    'icon'    => [
                        'title' => __('warehouses'),
                        'icon'  => 'fal fa-warehouse'
                    ],
                    'actions' => [
                        $this->canEdit && $request->route()->routeName == 'grp.org.warehouses.index' ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new warehouse'),
                            'label'   => __('warehouse'),
                            'route'   => [
                                'name'       => 'grp.org.warehouses.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                    ]
                ],
                'data'        => WarehouseResource::collection($warehouses),
            ]
        )->table($this->tableStructure($this->parent));
    }

    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        return array_merge(
            (new ShowInventoryDashboard())->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.warehouses.index',
                            'parameters' => $routeParameters
                        ],
                        'label' => __('warehouses'),
                        'icon'  => 'fal fa-bars',
                    ],
                    'suffix' => $suffix

                ]
            ]
        );
    }
}
