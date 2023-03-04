<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 15 Sept 2022 18:56:59 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\WarehouseArea;

use App\Actions\InertiaAction;
use App\Actions\Inventory\ShowInventoryDashboard;
use App\Actions\Inventory\Warehouse\ShowWarehouse;
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
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('inventory.view')
            );
    }


    public function inOrganisation(): LengthAwarePaginator
    {
        $this->validateAttributes();
        $this->parent = app('currentTenant');

        return $this->handle();
    }

    public function inWarehouse(Warehouse $warehouse): LengthAwarePaginator
    {
        $this->parent = $warehouse;
        $this->validateAttributes();

        return $this->handle();
    }


    public function jsonResponse(LengthAwarePaginator $warehousesAreas): AnonymousResourceCollection
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


    public function getBreadcrumbs(string $routeName, Warehouse|Tenant $parent): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route' => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel' => [
                        'label' => __('areas')
                    ]
                ],
            ];
        };


        return match ($routeName) {
            'inventory.warehouse_areas.index' =>
            array_merge(
                (new ShowInventoryDashboard())->getBreadcrumbs(),
                $headCrumb()
            ),
            'inventory.warehouses.show.warehouse_areas.index' =>
            array_merge(
                (new ShowWarehouse())->getBreadcrumbs($parent),
                $headCrumb([$parent->slug])
            ),
            default => []
        };
    }

}
