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
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Inventory\LocationResource;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexLocations extends InertiaAction
{
    public function handle($parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('locations.code', 'LIKE', "%$value%");
            });
        });


        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::LOCATIONS->value);

        return QueryBuilder::for(Location::class)
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
            ->withQueryString();
    }


    public function tableStructure(): Closure
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

    public function inTenant(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(parent: app('currentTenant'));
    }

    public function inWarehouse(Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(parent: $warehouse);
    }

    public function inWarehouseArea(WarehouseArea $warehouseArea, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(parent: $warehouseArea);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouseInWarehouseArea(Warehouse $warehouse, WarehouseArea $warehouseArea, ActionRequest $request): LengthAwarePaginator
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

    public function jsonResponse(LengthAwarePaginator $locations): AnonymousResourceCollection
    {
        return LocationResource::collection($locations);
    }


    public function htmlResponse(LengthAwarePaginator $locations, ActionRequest $request)
    {
        return Inertia::render(
            'Inventory/Locations',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('locations'),
                'pageHead'    => [
                    'title'  => __('locations'),
                    'create' => $this->canEdit
                    && (
                        $this->routeName == 'inventory.locations.index'                 or
                        $this->routeName == 'inventory.warehouses.show.locations.index' or
                        $this->routeName == 'inventory.warehouses.show.warehouse-areas.show.locations.index'
                    )
                            ? [
                        'route' => [
                            'name'       => 'inventory.locations.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label' => __('locations')
                    ] : false,
                ],
                'data'        => LocationResource::collection($locations),


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
                         'label' => __('locations'),
                         'icon'  => 'fal fa-bars'
                     ],
                ],
            ];
        };
        return match ($routeName) {
            'inventory.locations.index' =>
            array_merge(
                (new InventoryDashboard())->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'=> 'inventory.locations.index',
                        null
                    ]
                )
            ),
            'inventory.warehouses.show.locations.index' =>
            array_merge(
                (new ShowWarehouse())->getBreadcrumbs($routeParameters['warehouse']),
                $headCrumb([
                    'name'      => 'inventory.warehouses.show.locations.index',
                    'parameters'=>
                        [
                            $routeParameters['warehouse']->slug
                        ]
                ])
            ),
            'inventory.warehouse-areas.show.locations.index' =>
            array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs(
                    'inventory.warehouse-areas.show',
                    [
                      'warehouseArea' => $routeParameters['warehouseArea']
                    ]
                ),
                $headCrumb([
                    'name'      => 'inventory.warehouse-areas.show.locations.index',
                    'parameters'=>
                        [
                            $routeParameters['warehouse']->slug
                        ]
                ])
            ),
            'inventory.warehouses.show.warehouse-areas.show.locations.index' =>
            array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs(
                    'inventory.warehouses.show.warehouse-areas.show',
                    [
                        'warehouse'     => $routeParameters['warehouse'],
                        'warehouseArea' => $routeParameters['warehouseArea']
                    ]
                ),
                $headCrumb([
                    'name'      => 'inventory.warehouses.show.warehouse-areas.show.locations.index',
                    'parameters'=>
                        [
                            $routeParameters['warehouse']->slug
                        ]                ])
            ),

            default => []
        };
    }
}
