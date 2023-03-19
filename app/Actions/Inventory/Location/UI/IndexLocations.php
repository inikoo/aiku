<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 11:34:32 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\UI;

use App\Actions\InertiaAction;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Inventory\LocationResource;
use App\Models\Central\Tenant;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Closure;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexLocations extends InertiaAction
{
    use HasUILocations;

    public function handle(WarehouseArea|Warehouse|Tenant $parent): AnonymousResourceCollection
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('locations.code', 'LIKE', "%$value%");
            });
        });


        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::LOCATIONS->value);

        return LocationResource::collection(
            QueryBuilder::for(Location::class)
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
                ->paginate(
                    perPage: $this->perPage ?? config('ui.table.records_per_page'),
                    pageName: TabsAbbreviationEnum::LOCATIONS->value.'Page'
                )
                ->withQueryString()
        );
    }


    public function locationsTableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $table
                ->name(TabsAbbreviationEnum::LOCATIONS->value)
                ->pageName(TabsAbbreviationEnum::LOCATIONS->value.'Page')
                ->withGlobalSearch()
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }

    public function inTenant(ActionRequest $request): AnonymousResourceCollection
    {
        $this->initialisation($request);

        return $this->handle(parent: app('currentTenant'));
    }

    public function inWarehouse(Warehouse $warehouse, ActionRequest $request): AnonymousResourceCollection
    {
        $this->initialisation($request);

        return $this->handle(parent: $warehouse);
    }

    public function inWarehouseArea(WarehouseArea $warehouseArea, ActionRequest $request): AnonymousResourceCollection
    {
        $this->initialisation($request);

        return $this->handle(parent: $warehouseArea);
    }


    public function inWarehouseInWarehouseArea(Warehouse $warehouse, WarehouseArea $warehouseArea, ActionRequest $request): AnonymousResourceCollection
    {
        $this->initialisation($request);

        return $this->handle(parent: $warehouseArea);
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.locations.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('inventory.view')
            );
    }

    public function jsonResponse($locations): AnonymousResourceCollection
    {
        return $locations;
    }


    public function htmlResponse(AnonymousResourceCollection $locations, ActionRequest $request)
    {
        return Inertia::render(
            'Inventory/Locations',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), last($request->route()->parameters())),
                'title'       => __('locations'),
                'pageHead'    => [
                    'title'  => __('locations'),
                    'create' => $this->canEdit && $this->routeName == 'inventory.locations.index' ? [
                        'route' => [
                            'name'       => 'inventory.locations.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label' => __('locations')
                    ] : false,
                ],
                'data'        => $locations,


            ]
        )->table($this->locationsTableStructure());
    }
}
