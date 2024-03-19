<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 11:34:32 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\UI;

use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\Inventory\WarehouseArea\UI\ShowWarehouseArea;
use App\Actions\OrgAction;
use App\Enums\UI\WarehouseAreaTabsEnum;
use App\Enums\UI\WarehouseTabsEnum;
use App\Http\Resources\Inventory\LocationsResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexLocations extends OrgAction
{
    private Warehouse|WarehouseArea|Organisation $parent;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("inventories.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("inventories.{$this->organisation->id}.edit");
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(WarehouseTabsEnum::values());

        return $this->handle(parent: $warehouse);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouseArea(Organisation $organisation, Warehouse $warehouse, WarehouseArea $warehouseArea, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $warehouseArea;
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(WarehouseTabsEnum::values());

        return $this->handle(parent: $warehouseArea);
    }


    public function asController(Organisation $organisation, Warehouse $warehouse, WarehouseArea $warehouseArea, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $warehouseArea;
        $this->initialisation($organisation, $request)->withTab(WarehouseAreaTabsEnum::values());

        return $this->handle(parent: $warehouseArea);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function fromPallet(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, Pallet $pallet, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $pallet->warehouse;
        $this->initialisation($organisation, $request)->withTab(WarehouseAreaTabsEnum::values());

        return $this->handle(parent: $this->parent);
    }


    public function handle(Warehouse|WarehouseArea|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('locations.code', 'LIKE', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Location::class);

        return $queryBuilder
            ->defaultSort('locations.code')
            ->select(
                [
                    'locations.id',
                    'locations.code',
                    'locations.slug',
                    'allow_stocks',
                    'allow_fulfilment',
                    'allow_dropshipping',
                    'has_stocks',
                    'has_fulfilment',
                    'has_dropshipping',
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
                        'Warehouse' => [
                            'title'       => __("No locations found"),
                            'description' => $this->canEdit ? __('Get started by creating a new location. ✨')
                                : null,
                            'count'       => $parent->stats->number_locations,
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
                                    'name'       => 'grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.create',
                                    'parameters' => [
                                        'organisation'  => $parent->organisation->slug,
                                        'warehouse'     => $parent->warehouse->slug,
                                        'warehouseArea' => $parent->slug
                                    ]
                                ]
                            ] : null
                        ],
                        default => null
                    }
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'scope', label: __('scope'), canBeHidden: false)
                ->defaultSort('code');
        };
    }


    public function jsonResponse(LengthAwarePaginator $locations): AnonymousResourceCollection
    {
        return LocationsResource::collection($locations);
    }


    /**
     * @throws \Exception
     */
    public function htmlResponse(LengthAwarePaginator $locations, ActionRequest $request): Response
    {
        $scope     = $this->parent;
        $container = null;
        if (class_basename($scope) == 'Warehouse') {
            $container = [
                'icon'    => ['fal', 'fa-warehouse'],
                'tooltip' => __('Warehouse'),
                'label'   => Str::possessive($scope->code)
            ];
        } elseif (class_basename($scope) == 'WarehouseArea') {
            $container = [
                'icon'    => ['fal', 'fa-map-signs'],
                'tooltip' => __('Warehouse Area'),
                'label'   => Str::possessive($scope->code)
            ];
        }

        return Inertia::render(
            'Org/Warehouse/Locations',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('locations'),
                'pageHead'    => [
                    'title'     => __('locations'),
                    'container' => $container,
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-inventory'],
                        'title' => __('locations')
                    ],
                    'actions'   => [
                        $this->canEdit
                        && (
                            $request->route()->getName() == 'grp.org.warehouses.show.infrastructure.locations.index' or
                            $request->route()->getName() == 'grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.index'
                        )
                            ? [
                            'type'   => 'buttonGroup',
                            'key'    => 'upload-add',
                            'button' => [
                                [
                                    'type'  => 'button',
                                    'style' => 'primary',
                                    'icon'  => ['fal', 'fa-upload'],
                                    'label' => 'upload',
                                    'route' => match ($this->parent::class) {
                                        Warehouse::class => [
                                            'name'       => 'grp.models.warehouse.location.upload',
                                            'parameters' => [
                                                $this->parent->id
                                            ]
                                        ],
                                        WarehouseArea::class => [
                                            'name'       => 'grp.models.warehouse-area.location.upload',
                                            'parameters' => [
                                                $this->parent->id
                                            ]
                                        ],
                                        default => throw new \Exception('Unexpected match value')
                                    }
                                ],
                                [
                                    'type'  => 'button',
                                    'style' => 'create',
                                    'label' => __('location'),
                                    'route' => match ($request->route()->getName()) {
                                        'grp.org.warehouses.show.infrastructure.locations.index' => [
                                            'name'       => 'grp.org.warehouses.show.infrastructure.locations.create',
                                            'parameters' => array_values($request->route()->originalParameters())
                                        ],
                                        default => [
                                            'name'       => 'grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.create',
                                            'parameters' => array_values($request->route()->originalParameters())
                                        ]
                                    }
                                ]
                            ]
                        ] : null
                    ]
                ],
                'data'        => LocationsResource::collection($locations),

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
            'grp.org.warehouses.show.infrastructure.locations.index' =>
            array_merge(
                (new ShowWarehouse())->getBreadcrumbs(Arr::only($routeParameters, ['organisation', 'warehouse'])),
                $headCrumb([
                    'name'       => 'grp.org.warehouses.show.infrastructure.locations.index',
                    'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse'])
                ])
            ),
            'grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.index' =>
            array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs(
                    Arr::only($routeParameters, ['organisation', 'warehouse', 'warehouseArea'])
                ),
                $headCrumb([
                    'name'       => 'grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.index',
                    'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse', 'warehouseArea'])
                ])
            ),

            default => []
        };
    }
}
