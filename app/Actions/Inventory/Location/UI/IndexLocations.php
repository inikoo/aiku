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
use App\Actions\Overview\ShowGroupOverviewHub;
use App\Enums\UI\Inventory\WarehouseAreaTabsEnum;
use App\Enums\UI\Inventory\WarehouseTabsEnum;
use App\Http\Resources\Inventory\LocationsResource;
use App\Http\Resources\Tag\TagResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\Tags\Tag;

class IndexLocations extends OrgAction
{
    private Group|Warehouse|WarehouseArea|Organisation $parent;

    public function authorize(ActionRequest $request): bool
    {

        if ($this->parent instanceof Group) {
            return $request->user()->authTo("group-overview");
        } elseif ($this->parent instanceof Organisation) {
            $this->canEdit = $request->user()->authTo('org-supervisor.'.$this->organisation->id);
            return  $request->user()->authTo("warehouses-view.{$this->organisation->id}");
        }

        $this->canEdit = $request->user()->authTo("locations.{$this->warehouse->id}.edit");
        return  $request->user()->authTo("locations.{$this->warehouse->id}.edit");

    }

    public function maya(Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->maya   = true;
        $this->parent = $warehouse;
        $this->initialisation($this->parent, $request);

        return $this->handle(parent: $warehouse);
    }

    public function inGroup(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = group();
        $this->scope = $this->parent;
        $this->initialisationFromGroup(group(), $request)->withTab(WarehouseAreaTabsEnum::values());

        return $this->handle($this->parent);
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
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(WarehouseAreaTabsEnum::values());

        return $this->handle(parent: $warehouseArea);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function fromPallet(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, Pallet $pallet, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $pallet->warehouse;
        $this->initialisation($organisation, $request)->withTab(WarehouseAreaTabsEnum::values());

        return $this->handle(parent: $this->parent);
    }


    public function handle(Group|Warehouse|WarehouseArea|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('locations.code', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Location::class);

        if ($parent instanceof Group) {
            $queryBuilder->where('locations.group_id', $parent->id);
        }

        return $queryBuilder
            ->defaultSort('locations.code')
            ->select(
                [
                    'locations.id',
                    'locations.code',
                    'locations.slug',
                    'locations.stock_value',
                    'locations.stock_commercial_value',
                    'locations.max_weight',
                    'locations.max_volume',
                    'locations.allow_stocks',
                    'locations.allow_fulfilment',
                    'locations.allow_dropshipping',
                    'locations.has_stock_slots',
                    'locations.has_fulfilment',
                    'locations.has_dropshipping_slots',
                    'locations.max_weight',
                    'locations.max_volume',
                    'warehouses.slug as warehouse_slug',
                    'warehouse_areas.slug as warehouse_area_slug',
                    'warehouse_area_id',
                    'organisations.slug as organisation_slug',
                    'organisations.name as organisation_name'
                ]
            )
            ->leftJoin('organisations', 'locations.organisation_id', 'organisations.id')
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
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }


    public function tableStructure(Group|Warehouse|WarehouseArea|Organisation $parent, ?array $modelOperations = null, $prefix = null): Closure
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
                                    'name'       => 'grp.org.warehouses.show.infrastructure.warehouse_areas.show.locations.create',
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
                ->column(key: 'scope', label: __('scope'), canBeHidden: false, type: 'icon')
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'stock_value', label: __('Stock'), canBeHidden: false, type: 'icon')
                ->column(key: 'max_weight', label: __('Weight'), canBeHidden: false, type: 'icon')
                ->column(key: 'max_volume', label: __("CBM (Cubic's meter)"), canBeHidden: false, type: 'icon');
            if ($parent instanceof Group) {
                $table->column(key: 'organisation_name', label: __('organisation'), canBeHidden: false, sortable: true, searchable: true);
            }
            $table->column(key: 'max_weight', label: __('weight'), canBeHidden: false);
            $table->column(key: 'max_volume', label: __('volume'), canBeHidden: false);
            $table->column(key: '', label: __('stock value'), canBeHidden: false);
            $table->column(key: 'tags', label: __('tags'), canBeHidden: false)
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

        $icon = [
            'icon'  => ['fal', 'fa-inventory'],
            'title' => __('locations')
        ];

        if ($scope instanceof Group) {
            $icon = [
                'icon'  => ['fal', 'fa-location-arrow'],
                'title' => __('locations')
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
                    'icon'      => $icon,
                    'actions'   => [
                        $this->canEdit
                        && (
                            $request->route()->getName() == 'grp.org.warehouses.show.infrastructure.locations.index' or
                            $request->route()->getName() == 'grp.org.warehouses.show.infrastructure.warehouse_areas.show.locations.index'
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
                                            'name'       => 'grp.models.warehouse_area.location.upload',
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
                                            'name'       => 'grp.org.warehouses.show.infrastructure.warehouse_areas.show.locations.create',
                                            'parameters' => array_values($request->route()->originalParameters())
                                        ]
                                    }
                                ]
                            ]
                        ] : null
                    ]
                ],

                'tagRoute'   => [
                    'store' => [
                        'name'       => 'grp.models.location.tag.store',
                        'parameters' => []
                    ],
                    'update' => [
                        'name'       => 'grp.models.location.tag.attach',
                        'parameters' => []
                    ],
                ],
                'tagsList'    => TagResource::collection(Tag::where('type', 'inventory')->get()),
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
                        'label' => __('Locations'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.overview.inventory.locations.index' =>
            array_merge(
                ShowGroupOverviewHub::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ]
                )
            ),
            'grp.org.warehouses.show.infrastructure.locations.index' =>
            array_merge(
                (new ShowWarehouse())->getBreadcrumbs(Arr::only($routeParameters, ['organisation', 'warehouse'])),
                $headCrumb([
                    'name'       => 'grp.org.warehouses.show.infrastructure.locations.index',
                    'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse'])
                ])
            ),
            'grp.org.warehouses.show.infrastructure.warehouse_areas.show.locations.index' =>
            array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs(
                    Arr::only($routeParameters, ['organisation', 'warehouse', 'warehouseArea'])
                ),
                $headCrumb([
                    'name'       => 'grp.org.warehouses.show.infrastructure.warehouse_areas.show.locations.index',
                    'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse', 'warehouseArea'])
                ])
            ),

            default => []
        };
    }
}
