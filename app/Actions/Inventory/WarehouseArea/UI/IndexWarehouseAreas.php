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
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Inventory\WarehouseAreaResource;
use App\Models\Central\Tenant;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexWarehouseAreas extends InertiaAction
{
    public function handle(Warehouse|Tenant $parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('warehouse_areas.name', 'LIKE', "%$value%")
                    ->orWhere('warehouse_areas.code', 'LIKE', "%$value%");
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::WAREHOUSE_AREAS->value);



        return QueryBuilder::for(WarehouseArea::class)
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
            ->paginate(
                //  perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::WAREHOUSE_AREAS->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $table
                ->name(TabsAbbreviationEnum::WAREHOUSE_AREAS->value)
                ->pageName(TabsAbbreviationEnum::WAREHOUSE_AREAS->value.'Page')
                ->withGlobalSearch()
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_locations', label: __('locations'), canBeHidden: false, sortable: true)
                ->defaultSort('code');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.warehouses.edit');

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


    public function jsonResponse(LengthAwarePaginator $warehousesAreas): AnonymousResourceCollection
    {
        return WarehouseAreaResource::collection($warehousesAreas);
    }


    public function htmlResponse(LengthAwarePaginator $warehousesAreas, ActionRequest $request)
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());

        return Inertia::render(
            'Inventory/WarehouseAreas',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('warehouse areas'),
                'pageHead'    => [
                    'title'  => __('warehouse areas'),
                    'create' => $this->canEdit && $this->routeName == 'inventory.warehouse-areas.index' ? [
                        'route' => [
                            'name'       => 'inventory.warehouse-areas.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label' => __('warehouse areas')
                    ] : false,
                ],
                'data'        => WarehouseAreaResource::collection($warehousesAreas)


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
                        'name'=> 'inventory.warehouse-areas.index',
                        null
                    ]
                )
            ),
            'inventory.warehouses.show.warehouse-areas.index' ,
            =>
            array_merge(
                (new ShowWarehouse())->getBreadcrumbs($routeParameters['warehouse']),
                $headCrumb([
                    'name'      => 'inventory.warehouses.show.warehouse-areas.index',
                    'parameters'=>
                        [
                            $routeParameters['warehouse']->slug
                        ]
                ])
            ),
            default => []
        };
    }
}
