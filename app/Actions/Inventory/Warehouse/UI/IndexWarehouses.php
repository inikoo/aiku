<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 15:30:55 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Warehouse\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\OrgAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Enums\Inventory\Warehouse\WarehouseStateEnum;
use App\Enums\UI\Inventory\WarehousesTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Inventory\WarehousesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexWarehouses extends OrgAction
{
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
        $this->initialisation($organisation, $request)->withTab(WarehousesTabsEnum::values());
        return $this->handle($organisation, 'warehouses');
    }

    protected function getElementGroups(Organisation|Group $parent): array
    {
        return [
            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    WarehouseStateEnum::labels(),
                    WarehouseStateEnum::count($parent)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('warehouses.state', $elements);
                }
            ],
        ];
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


        foreach ($this->getElementGroups($organisation) as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        return $queryBuilder
            ->defaultSort('warehouses.code')
            ->select([
                'warehouses.code as code',
                'warehouses.id',
                'warehouses.name',
                'warehouse_stats.number_warehouse_areas',
                'warehouse_stats.number_locations',
                'warehouses.slug as slug',
                'warehouses.state as state'
            ])
            ->leftJoin('warehouse_stats', 'warehouse_stats.warehouse_id', 'warehouses.id')
            ->allowedSorts(['code', 'name', 'number_warehouse_areas', 'number_locations'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation $organisation, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($organisation, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            foreach ($this->getElementGroups($organisation) as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements']
                );
            }

            $table
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title'       => __('no warehouses'),
                        'description' => $this->canEdit ? __('Get started by creating a new warehouse.') : null,
                        'count'       => $organisation->inventoryStats->number_warehouses,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new warehouse'),
                            'label'   => __('warehouse'),
                            'route'   => [
                                'name'       => 'grp.org.warehouses.create',
                                'parameters' => $organisation->slug
                            ]
                        ] : null
                    ]
                )
                ->column(key: 'state', label: __(''), canBeHidden: false, sortable: false, searchable: false, type: 'avatar')
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
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => WarehousesTabsEnum::navigation(),
                ],

                WarehousesTabsEnum::WAREHOUSES->value => $this->tab == WarehousesTabsEnum::WAREHOUSES->value ?
                    fn () => WarehousesResource::collection($warehouses)
                    : Inertia::lazy(fn () => WarehousesResource::collection($warehouses)),


                WarehousesTabsEnum::WAREHOUSES_HISTORIES->value => $this->tab == WarehousesTabsEnum::WAREHOUSES_HISTORIES->value ?
                    fn () => HistoryResource::collection(IndexHistory::run(Warehouse::class, 'hst'))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run(Warehouse::class, 'hst')))



            ]
        )->table($this->tableStructure(
            organisation:$this->organisation,
            prefix:WarehousesTabsEnum::WAREHOUSES->value
        ))
        ->table(IndexHistory::make()->tableStructure('hst'));
    }

    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        return array_merge(
            ShowDashboard::make()->getBreadcrumbs(),
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
