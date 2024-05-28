<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Apr 2024 10:14:33 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplier\UI;

use App\Actions\OrgAction;
use App\Actions\Procurement\OrgAgent\UI\ShowOrgAgent;
use App\Actions\Procurement\UI\ProcurementDashboard;
use App\Http\Resources\Procurement\SupplierResource;
use App\InertiaTable\InertiaTable;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgSupplier;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexOrgSuppliers extends OrgAction
{
    private Organisation|OrgAgent $parent;

    protected function getSupplierElementGroups(Organisation|OrgAgent $parent): array
    {
        return
            [
                'status' => [
                    'label'    => __('status'),
                    'elements' => [
                        'active'   => [__('active'), $parent->stats->number_suppliers],
                        'archived' => [__('archived'), $parent->stats->number_archived_suppliers]
                    ],

                    'engine' => function ($query, $elements) {
                        $query->whereIn('state', $elements);
                    }

                ],

            ];
    }

    public function handle(OrgAgent|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('suppliers.code', $value)
                    ->orWhereAnyWordStartWith('suppliers.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }


        $queryBuilder = QueryBuilder::for(OrgSupplier::class);


        if (class_basename($parent) == 'OrgAgent') {
            $queryBuilder->where('suppliers.owner_id', $parent->id);
            $queryBuilder->leftJoin('agents', 'suppliers.owner_id', 'agents.id');
            $queryBuilder->addSelect(['agents.slug as agent_slug', 'agents.name as agent_name']);
        } else {
            $queryBuilder->where('org_suppliers.organisation_id', $parent->id);
        }

        foreach ($this->getSupplierElementGroups($parent) as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        return $queryBuilder
            ->defaultSort('suppliers.code')
            ->select(['suppliers.code', 'suppliers.slug', 'suppliers.name', 'suppliers.location as supplier_locations', 'number_supplier_products', 'number_purchase_orders'])
            ->leftJoin('suppliers', 'org_suppliers.supplier_id', 'suppliers.id')

            ->leftJoin('org_supplier_stats', 'org_supplier_stats.org_supplier_id', 'org_suppliers.id')
            ->allowedSorts(['code', 'name', 'agent_name', 'supplier_locations', 'number_supplier_products', 'number_purchase_orders'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation|OrgAgent $parent, array $modelOperations = null, $prefix = null, $canEdit = false): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix, $parent, $canEdit) {
            if ($parent instanceof OrgAgent) {
                $organisation = $parent->organisation;
            } else {
                $organisation = $parent;
            }

            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            foreach ($this->getSupplierElementGroups($parent) as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements']
                );
            }

            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title' => __('no suppliers'),
                        'count' => $organisation->inventoryStats->number_warehouse_areas,

                    ]
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'supplier_locations', label: __('location'), canBeHidden: false)
                ->column(key: 'number_supplier_products', label: __('products'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_purchase_orders', label: __('purchase orders'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle($organisation);
    }

    public function inOrgAgent(Organisation $organisation, OrgAgent $orgAgent, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $orgAgent;

        $this->initialisation($organisation, $request);
        $this->getSupplierElementGroups($orgAgent);

        return $this->handle($orgAgent);
    }

    public function jsonResponse(LengthAwarePaginator $suppliers): AnonymousResourceCollection
    {
        return SupplierResource::collection($suppliers);
    }


    public function htmlResponse(LengthAwarePaginator $suppliers, ActionRequest $request): Response
    {
        return Inertia::render(
            'Procurement/OrgSuppliers',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('suppliers'),
                'pageHead'    => [
                    'icon'  => ['fal', 'fa-person-dolly'],
                    'title' => __('suppliers'),
                ],
                'data'        => SupplierResource::collection($suppliers),


            ]
        )->table($this->tableStructure($this->parent));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return match ($routeName) {
            'grp.org.procurement.org_suppliers.index' => array_merge(
                ProcurementDashboard::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.org_suppliers.index',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('suppliers'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            ),
            'grp.org.procurement.org_agents.show.org_suppliers.index' => array_merge(
                ShowOrgAgent::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.org_agents.show.org_suppliers.index',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('suppliers'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            )
        };
    }
}
