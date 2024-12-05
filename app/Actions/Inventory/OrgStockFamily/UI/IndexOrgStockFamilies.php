<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 05 Aug 2024 14:56:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStockFamily\UI;

use App\Actions\Inventory\HasInventoryAuthorisation;
use App\Actions\Inventory\UI\ShowInventoryDashboard;
use App\Actions\OrgAction;
use App\Enums\Inventory\OrgStockFamily\OrgStockFamilyStateEnum;
use App\Http\Resources\Inventory\OrgStockFamiliesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Inventory\OrgStockFamily;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexOrgStockFamilies extends OrgAction
{
    use HasInventoryAuthorisation;
    private string $bucket;

    public function asController(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromWarehouse($warehouse, $request);
        $this->bucket = 'all';

        return $this->handle($organisation);
    }

    public function active(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromWarehouse($warehouse, $request);
        $this->bucket = 'active';

        return $this->handle($organisation);
    }

    public function inProcess(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromWarehouse($warehouse, $request);
        $this->bucket = 'in_process';

        return $this->handle($organisation);
    }

    public function discontinuing(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromWarehouse($warehouse, $request);
        $this->bucket = 'discontinuing';

        return $this->handle($organisation);
    }

    public function discontinued(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromWarehouse($warehouse, $request);
        $this->bucket = 'discontinued';

        return $this->handle($organisation);
    }

    public function maya(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->maya   = true;
        $this->initialisation($organisation, $request);
        $this->parent = $organisation;

        return $this->handle($organisation);
    }


    protected function getElementGroups(Organisation $organisation): array
    {
        return
            [
                'state' => [
                    'label'    => __('State'),
                    'elements' => array_merge_recursive(
                        OrgStockFamilyStateEnum::labels(),
                        OrgStockFamilyStateEnum::count($organisation)
                    ),
                    'engine'   => function ($query, $elements) {
                        $query->whereIn('org_stock_families.state', $elements);
                    }
                ]
            ];
    }

    public function handle(Organisation $organisation, $prefix = null, $bucket = null): LengthAwarePaginator
    {
        if ($bucket) {
            $this->bucket = $bucket;
        }

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('org_stock_families.code', $value)
                    ->orWhereAnyWordStartWith('org_stock_families.name', $value);
            });
        });
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(OrgStockFamily::class);
        $queryBuilder->where('org_stock_families.organisation_id', $organisation->id);

        if ($this->bucket == 'active') {
            $queryBuilder->where('org_stock_families.state', OrgStockFamilyStateEnum::ACTIVE);
        } elseif ($this->bucket == 'discontinuing') {
            $queryBuilder->where('org_stock_families.state', OrgStockFamilyStateEnum::DISCONTINUING);
        } elseif ($this->bucket == 'discontinued') {
            $queryBuilder->where('org_stock_families.state', OrgStockFamilyStateEnum::DISCONTINUED);
        } elseif ($this->bucket == 'in_process') {
            $queryBuilder->where('org_stock_families.state', OrgStockFamilyStateEnum::IN_PROCESS);
        } else {
            foreach ($this->getElementGroups($organisation) as $key => $elementGroup) {
                $queryBuilder->whereElementGroup(
                    key: $key,
                    allowedElements: array_keys($elementGroup['elements']),
                    engine: $elementGroup['engine'],
                    prefix: $prefix
                );
            }
        }

        return $queryBuilder
           ->defaultSort('code')
           ->select([
               'slug',
               'code',
               'org_stock_families.id as id',
               'name',
               'number_current_org_stocks'
           ])
           ->leftJoin('org_stock_family_stats', 'org_stock_family_stats.org_stock_family_id', 'org_stock_families.id')
           ->allowedSorts(['code', 'name', 'number_current_org_stocks'])
           ->allowedFilters([$globalSearch])
           ->withPaginator($prefix)
           ->withQueryString();
    }

    public function tableStructure(Organisation $organisation, $prefix = null, $bucket = 'all'): Closure
    {
        return function (InertiaTable $table) use ($organisation, $prefix, $bucket) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            if ($bucket == 'all') {
                foreach ($this->getElementGroups($organisation) as $key => $elementGroup) {
                    $table->elementGroup(
                        key: $key,
                        label: $elementGroup['label'],
                        elements: $elementGroup['elements']
                    );
                }
            }

            $table
                ->withGlobalSearch()
                ->column(key: 'code', label: 'code', canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_current_org_stocks', label: 'SKUs', canBeHidden: false, sortable: true)
                ->defaultSort('code');
        };
    }

    public function jsonResponse(LengthAwarePaginator $stocks): AnonymousResourceCollection
    {
        return OrgStockFamiliesResource::collection($stocks);
    }

    public function getOrgStockFamiliesSubNavigation(): array
    {
        return [

            [
                'label'  => __('Active'),
                'root'   => 'grp.org.warehouses.show.inventory.org_stock_families.active.',
                'route'   => [
                    'name'       => 'grp.org.warehouses.show.inventory.org_stock_families.active.index',
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'warehouse'    => $this->warehouse->slug
                    ]
                ],
                'number' => $this->organisation->inventoryStats->number_org_stock_families_state_active ?? 0
            ],
            [
                'label'  => __('In process'),
                'root'   => 'grp.org.warehouses.show.inventory.org_stock_families.in-process.',
                'route'   => [
                    'name'       => 'grp.org.warehouses.show.inventory.org_stock_families.in-process.index',
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'warehouse'    => $this->warehouse->slug
                    ]
                ],
                'number' => $this->organisation->inventoryStats->number_org_stock_families_state_in_process ?? 0
            ],
            [
                'label'  => __('Discontinuing'),
                'root'   => 'grp.org.warehouses.show.inventory.org_stock_families.discontinuing.',
                'route'   => [
                    'name'       => 'grp.org.warehouses.show.inventory.org_stock_families.discontinuing.index',
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'warehouse'    => $this->warehouse->slug
                    ]
                ],
                'number' => $this->organisation->inventoryStats->number_org_stock_families_state_discontinuing ?? 0
            ],
            [
                'label'  => __('Discontinued'),
                'root'   => 'grp.org.warehouses.show.inventory.org_stock_families.discontinued.',
                'align'  => 'right',
                'route'   => [
                    'name'       => 'grp.org.warehouses.show.inventory.org_stock_families.discontinued.index',
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'warehouse'    => $this->warehouse->slug
                    ]
                ],
                'number' => $this->organisation->inventoryStats->number_org_stock_families_state_discontinued ?? 0
            ],
            [
                'label'  => __('All'),
                'icon'   => 'fal fa-bars',
                'root'   => 'grp.org.warehouses.show.inventory.org_stock_families.index',
                'align'  => 'right',
                'route'   => [
                    'name'       => 'grp.org.warehouses.show.inventory.org_stock_families.index',
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'warehouse'    => $this->warehouse->slug
                    ]
                ],
                'number' => $this->organisation->inventoryStats->number_org_stock_families ?? 0

            ],

        ];
    }

    public function htmlResponse(LengthAwarePaginator $stockFamily, ActionRequest $request): Response
    {
        $organisation = $this->organisation;

        $subNavigation = $this->getOrgStockFamiliesSubNavigation();

        $title = match ($this->bucket) {
            'active'        => __('Active SKU Families'),
            'in_process'    => __('In process SKU Families'),
            'discontinuing' => __('Discontinuing SKU Families'),
            'discontinued'  => __('Discontinued SKU Families'),
            default         => __('SKU Families')
        };

        return Inertia::render(
            'Org/Inventory/OrgStockFamilies',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'         => $title,
                'pageHead'    => [
                    'title'         => $title,
                    'icon'    => [
                        'title' => __("SKUs families"),
                        'icon'  => 'fal fa-boxes-alt'
                    ],
                    'subNavigation' => $subNavigation
                ],
                'data'        => OrgStockFamiliesResource::collection($stockFamily),
            ]
        )->table($this->tableStructure($organisation));
    }

    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        return array_merge(
            ShowInventoryDashboard::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.warehouses.show.inventory.org_stock_families.index',
                            'parameters' => $routeParameters
                        ],
                        'label' => __("SKUs families"),
                        'icon'  => 'fal fa-bars',
                    ],
                    'suffix' => $suffix

                ]
            ]
        );
    }
}
