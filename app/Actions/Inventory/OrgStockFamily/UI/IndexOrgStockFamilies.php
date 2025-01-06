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
use App\Actions\Overview\ShowGroupOverviewHub;
use App\Enums\Inventory\OrgStockFamily\OrgStockFamilyStateEnum;
use App\Http\Resources\Inventory\OrgStockFamiliesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Inventory\OrgStockFamily;
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

class IndexOrgStockFamilies extends OrgAction
{
    use HasInventoryAuthorisation;
    private string $bucket;

    private Group|Organisation $parent;

    public function asController(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->bucket = 'all';
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($organisation);
    }

    public function active(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisationFromWarehouse($warehouse, $request);
        $this->bucket = 'active';

        return $this->handle($organisation);
    }

    public function inProcess(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisationFromWarehouse($warehouse, $request);
        $this->bucket = 'in_process';

        return $this->handle($organisation);
    }

    public function discontinuing(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisationFromWarehouse($warehouse, $request);
        $this->bucket = 'discontinuing';

        return $this->handle($organisation);
    }

    public function discontinued(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisationFromWarehouse($warehouse, $request);
        $this->bucket = 'discontinued';

        return $this->handle($organisation);
    }

    public function maya(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->maya   = true;
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle($organisation);
    }

    public function inGroup(ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'all';
        $this->parent = group();
        $this->initialisationFromGroup($this->parent, $request);

        return $this->handle($this->parent);
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

    public function handle(Group|Organisation $parent, $prefix = null, $bucket = null): LengthAwarePaginator
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
        if ($parent instanceof Group) {
            $queryBuilder->where('org_stock_families.group_id', $parent->id);
        } else {
            $queryBuilder->where('org_stock_families.group_id', $parent->id);
        }

        if ($this->bucket == 'active') {
            $queryBuilder->where('org_stock_families.state', OrgStockFamilyStateEnum::ACTIVE);
        } elseif ($this->bucket == 'discontinuing') {
            $queryBuilder->where('org_stock_families.state', OrgStockFamilyStateEnum::DISCONTINUING);
        } elseif ($this->bucket == 'discontinued') {
            $queryBuilder->where('org_stock_families.state', OrgStockFamilyStateEnum::DISCONTINUED);
        } elseif ($this->bucket == 'in_process') {
            $queryBuilder->where('org_stock_families.state', OrgStockFamilyStateEnum::IN_PROCESS);
        } elseif (!($this->parent instanceof Group)) {
            foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
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
               'org_stock_families.slug',
               'org_stock_families.code',
               'org_stock_families.id as id',
               'org_stock_families.name',
               'number_current_org_stocks',
               'organisations.name as organisation_name',
               'organisations.slug as organisation_slug',
               'warehouses.slug as warehouse_slug', // just work if the org has only one warehouse
           ])
           ->leftJoin('organisations', 'org_stock_families.organisation_id', 'organisations.id')
           ->leftJoin('warehouses', 'warehouses.organisation_id', 'organisations.id')
           ->leftJoin('org_stock_family_stats', 'org_stock_family_stats.org_stock_family_id', 'org_stock_families.id')
           ->allowedSorts(['code', 'name', 'number_current_org_stocks'])
           ->allowedFilters([$globalSearch])
           ->withPaginator($prefix)
           ->withQueryString();
    }

    public function tableStructure(Group|Organisation $parent, $prefix = null, $bucket = 'all'): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix, $bucket) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            if ($bucket == 'all' && !($parent instanceof Group)) {
                foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
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
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
            if ($parent instanceof Group) {
                $table->column(key: 'organisation_name', label: __('organisation'), canBeHidden: false, sortable: true, searchable: true);
            }
            $table->column(key: 'number_current_org_stocks', label: 'SKUs', canBeHidden: false, sortable: true)
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

        if ($this->parent instanceof Group) {
            $subNavigation = null;
            $title         = __('Org Stock Families');
            $titlePage     = __("Org Stock Families");
        } else {
            $subNavigation = $this->getOrgStockFamiliesSubNavigation();

            $title = match ($this->bucket) {
                'active'        => __('Active SKU Families'),
                'in_process'    => __('In process SKU Families'),
                'discontinuing' => __('Discontinuing SKU Families'),
                'discontinued'  => __('Discontinued SKU Families'),
                default         => __('SKU Families')
            };

            $titlePage = __("SKUs families");
        }

        return Inertia::render(
            'Org/Inventory/OrgStockFamilies',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'         => $title,
                'pageHead'    => [
                    'title'         => $title,
                    'icon'    => [
                        'title' => $titlePage,
                        'icon'  => 'fal fa-boxes-alt'
                    ],
                    'subNavigation' => $subNavigation
                ],
                'data'        => OrgStockFamiliesResource::collection($stockFamily),
            ]
        )->table($this->tableStructure($this->parent));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (array $routeParameters, ?string $suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => ($this->parent instanceof Group) ? __('Org Stock Families') : __("SKUs families"),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ]
            ];
        };

        return match ($routeName) {
            'grp.overview.inventory.org-stock-families.index' =>
            array_merge(
                ShowGroupOverviewHub::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),
            default => array_merge(
                ShowInventoryDashboard::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),
        };
    }
}
