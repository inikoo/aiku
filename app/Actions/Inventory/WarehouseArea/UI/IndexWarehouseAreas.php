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
use App\Models\Central\Tenant;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexWarehouseAreas extends InertiaAction
{
    use HasUIWarehouseAreas;

    private Warehouse|Tenant $parent;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('warehouse_areas.name', 'LIKE', "%$value%")
                    ->orWhere('warehouse_areas.code', 'LIKE', "%$value%");
            });
        });


        return QueryBuilder::for(WarehouseArea::class)
            ->defaultSort('warehouse_areas.code')
            ->select(['warehouse_areas.code', 'warehouse_areas.id', 'warehouse_areas.name', 'number_locations', 'warehouses.slug as warehouse_slug', 'warehouse_areas.slug'])
            ->leftJoin('warehouse_area_stats', 'warehouse_area_stats.warehouse_area_id', 'warehouse_areas.id')
            ->leftJoin('warehouses', 'warehouse_areas.warehouse_id', 'warehouses.id')
            ->when($this->parent, function ($query) {
                if (class_basename($this->parent) == 'Warehouse') {
                    $query->where('warehouse_areas.warehouse_id', $this->parent->id);
                }
            })
            ->allowedSorts(['warehouse_areas.code', 'warehouse_areas.name', 'number_locations'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.warehouse_areas.edit');
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('inventory.view')
            );
    }


    public function inOrganisation(ActionRequest $request): LengthAwarePaginator
    {
        //$this->validateAttributes();
        $this->parent = app('currentTenant');
        $this->initialisation($request);
        return $this->handle();
    }

    public function inWarehouse(Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $warehouse;
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle();
    }


    public function jsonResponse($warehousesAreas): AnonymousResourceCollection
    {
        return WarehouseAreaResource::collection($warehousesAreas);
    }


    public function htmlResponse(LengthAwarePaginator $warehousesAreas)
    {
        return Inertia::render(
            'Inventory/WarehouseAreas',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('warehouse areas'),
                'pageHead'    => [
                    'title' => __('warehouse areas'),
                    'create'  => $this->canEdit && $this->routeName=='inventory.warehouse_areas.index' ? [
                        'route' => [
                            'name'       => 'inventory.warehouse_areas.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('warehouse areas')
                    ] : false,
                ],
                'records'     => WarehouseAreaResource::collection($warehousesAreas),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_locations', label: __('locations'), canBeHidden: false, sortable: true)
                ->defaultSort('code');
        });
    }
}
