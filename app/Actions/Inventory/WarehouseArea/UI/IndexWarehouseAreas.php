<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 08:39:46 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\WarehouseArea\UI;

use App\Actions\Inventory\UI\ShowInventoryDashboard;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\OrgAction;
use App\Enums\UI\Inventory\WarehouseTabsEnum;
use App\Http\Resources\Inventory\WarehouseAreaResource;
use App\InertiaTable\InertiaTable;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexWarehouseAreas extends OrgAction
{
    protected Warehouse|Organisation $parent;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Organisation) {
            $this->canEdit = $request->user()->hasPermissionTo('org-supervisor.'.$this->organisation->id);

            return $request->user()->hasAnyPermission(
                [
                    'warehouses-view.'.$this->organisation->id,
                    'org-supervisor.'.$this->organisation->id
                ]
            );
        }

        $this->canEdit = $request->user()->hasPermissionTo("locations.{$this->warehouse->id}.edit");

        return $request->user()->hasPermissionTo("locations.{$this->warehouse->id}.edit");
    }

    public function maya(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->maya   = true;
        $this->initialisation($this->parent, $request);

        return $this->handle(parent: $organisation);
    }

    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request)->withTab(WarehouseTabsEnum::values());

        return $this->handle($organisation);
    }

    public function asController(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(WarehouseTabsEnum::values());

        return $this->handle($warehouse);
    }

    public function handle(Warehouse|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('warehouse_areas.code', $value)
                    ->whereWith('warehouse_areas.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(WarehouseArea::class);


        return $queryBuilder
            ->defaultSort('warehouse_areas.code')
            ->select(
                [
                    'warehouse_areas.code',
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

                if (class_basename($parent) == 'Organisation') {
                    $query->where('warehouse_areas.organisation_id', $parent->id);
                }
            })
            ->allowedSorts(['code', 'name', 'number_locations'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, ?array $modelOperations = null, $prefix = null): Closure
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
                        'Organisation' => [
                            'title'       => __("No warehouse areas found"),
                            'description' => $this->canEdit && $parent->stats->number_warehouses == 0 ? __('Get started by creating a warehouse area. ✨')
                                : __("In fact, is no even create a warehouse yet 🤷🏽‍♂️"),
                            'count'       => $parent->stats->number_warehouse_areas,
                            'action'      => $this->canEdit && $parent->stats->number_warehouses == 0 ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new warehouse'),
                                'label'   => __('warehouse'),
                                'route'   => [
                                    'name'       => 'grp.org.warehouses.create',
                                    'parameters' => [$parent->slug]
                                ]
                            ] : null
                        ],
                        'Warehouse' => [
                            'title'       => __("No warehouse areas found"),
                            'description' => $this->canEdit ? __('Get started by creating a new warehouse area. ✨')
                                : null,
                            'count'       => $parent->stats->number_warehouse_areas,
                            'action'      => $this->canEdit ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new warehouse area'),
                                'label'   => __('warehouse area'),
                                'route'   => [
                                    'name'       => 'grp.org.warehouses.show.infrastructure.warehouse-areas.create',
                                    'parameters' => [
                                        $parent->organisation->slug,
                                        $parent->slug
                                    ]
                                ]
                            ] : null
                        ],
                        default => null
                    }
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_locations', label: __('locations'), canBeHidden: false, sortable: true)
                ->defaultSort('code');
        };
    }

    public function jsonResponse(LengthAwarePaginator $warehouseAreas): AnonymousResourceCollection
    {
        return WarehouseAreaResource::collection($warehouseAreas);
    }

    public function htmlResponse(LengthAwarePaginator $warehouseAreas, ActionRequest $request): Response
    {
        $scope     = $this->parent;
        $container = null;
        if (class_basename($scope) == 'Warehouse') {
            $container = [
                'icon'    => ['fal', 'fa-warehouse'],
                'tooltip' => __('Warehouse'),
                'label'   => Str::possessive($scope->name)
            ];
        }

        return Inertia::render(
            'Org/Warehouse/WarehouseAreas',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('warehouse areas'),
                'pageHead'    => [
                    'title'     => __('warehouse areas'),
                    'container' => $container,
                    'icon'      => [
                        'icon'  => ['fal', 'fa-map-signs'],
                        'title' => __('warehouse areas')
                    ],
                    'actions'   => [
                        $this->canEdit && $request->route()->getName() == 'grp.org.warehouses.show.infrastructure.warehouse-areas.index' ? [
                            'type'   => 'buttonGroup',
                            'key'    => 'upload-add',
                            'button' => [
                                [
                                    'type'  => 'button',
                                    'style' => 'primary',
                                    'icon'  => ['fal', 'fa-upload'],
                                    'label' => 'upload',
                                    'route' => [
                                        'name'       => 'grp.models.warehouse.warehouse-areas.upload',
                                        'parameters' => [
                                            $this->parent->id
                                        ]
                                    ]
                                ],
                                [

                                    'type'  => 'button',
                                    'style' => 'create',
                                    'label' => __('areas'),
                                    'route' => [
                                        'name'       => 'grp.org.warehouses.show.infrastructure.warehouse-areas.create',
                                        'parameters' => $request->route()->originalParameters()
                                    ]

                                ]
                            ]
                        ] : null,
                    ]
                ],
                'data'        => WarehouseAreaResource::collection($warehouseAreas)
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
                        'label' => __('Warehouse areas'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.warehouse-areas.index' =>
            array_merge(
                (new ShowInventoryDashboard())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name' => 'grp.oms.warehouse-areas.index',
                        null
                    ]
                )
            ),
            'grp.org.warehouses.show.infrastructure.warehouse-areas.index',
            =>
            array_merge(
                ShowWarehouse::make()->getBreadcrumbs($routeParameters),
                $headCrumb([
                    'name'       => 'grp.org.warehouses.show.infrastructure.warehouse-areas.index',
                    'parameters' =>
                        [
                            $routeParameters['organisation'],
                            Warehouse::where('slug', $routeParameters['warehouse'])->first()->slug
                        ]
                ])
            ),
            default => []
        };
    }
}
