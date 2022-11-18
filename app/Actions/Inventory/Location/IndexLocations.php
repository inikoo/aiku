<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 16 Sept 2022 12:27:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location;

use App\Actions\InertiaAction;
use App\Actions\Inventory\ShowInventoryDashboard;
use App\Actions\Inventory\Warehouse\ShowWarehouse;
use App\Actions\Inventory\WarehouseArea\ShowWarehouseArea;
use App\Http\Resources\Inventory\LocationResource;
use App\Models\Central\Tenant;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class IndexLocations extends InertiaAction
{

    protected ?Warehouse $warehouse = null;
    protected WarehouseArea|Warehouse|Tenant|null $parent = null;


    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('locations.code', 'LIKE', "%$value%");
            });
        });


        return QueryBuilder::for(Location::class)
            ->defaultSort('locations.code')
            ->select(['locations.id', 'locations.code','locations.slug', 'warehouses.slug as warehouse_slug','warehouse_areas.slug as warehouse_area_slug', 'warehouse_area_id'])
            ->leftJoin('location_stats', 'location_stats.location_id', 'locations.id')
            ->leftJoin('warehouses', 'locations.warehouse_id', 'warehouses.id')
            ->leftJoin('warehouse_areas', 'locations.warehouse_area_id', 'warehouse_areas.id')
            ->when($this->parent, function ($query) {
                switch (class_basename($this->parent)) {
                    case 'WarehouseArea':
                        $query->where('locations.warehouse_area_id', $this->parent->id);
                        break;
                    case 'Warehouse':
                        $query->where('locations.warehouse_id', $this->parent->id);
                        break;
                }
            })
            ->allowedSorts(['code'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
    }


    public function inOrganisation(): LengthAwarePaginator
    {
        $this->validateAttributes();

        return $this->handle();
    }

    public function inWarehouse(Warehouse $warehouse): LengthAwarePaginator
    {
        $this->parent = $warehouse;

        $this->validateAttributes();

        return $this->handle();
    }

    public function inWarehouseArea(WarehouseArea $warehouseArea): LengthAwarePaginator
    {
        $this->parent = $warehouseArea;
        $this->validateAttributes();

        return $this->handle();
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function InWarehouseInWarehouseArea(Warehouse $warehouse, WarehouseArea $warehouseArea): LengthAwarePaginator
    {
        $this->parent = $warehouseArea;
        $this->validateAttributes();

        return $this->handle();
    }

    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('inventory.view')
            );
    }

    public function jsonResponse(LengthAwarePaginator $locations): AnonymousResourceCollection
    {
        return LocationResource::collection($locations);
    }

    public function htmlResponse(LengthAwarePaginator $locations)
    {
        return Inertia::render(
            'Inventory/Locations',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('locations'),
                'pageHead'    => [
                    'title' => __('locations'),
                ],
                'records'     => LocationResource::collection($locations),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        });
    }

    public function getBreadcrumbs(string $routeName, WarehouseArea|Warehouse|Tenant|null $parent): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route' => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel' => [
                        'label' => __('locations')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'inventory.locations.index' => array_merge(
                (new ShowInventoryDashboard())->getBreadcrumbs(),
                $headCrumb()
            ),
            'inventory.warehouses.show.locations.index' => array_merge(
                (new ShowWarehouse())->getBreadcrumbs($parent),
                $headCrumb([$parent->slug])
            ),
            'inventory.warehouse_areas.show.locations.index' => array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs('inventory.warehouse_areas.show', $parent),
                $headCrumb([$parent->slug])
            ),
            'inventory.warehouses.show.warehouse_areas.show.locations.index' => array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs('inventory.warehouses.show.warehouse_areas.show', $parent),
                $headCrumb([$parent->warehouse->slug, $parent->slug])
            ),

            default => []
        };
    }

}
