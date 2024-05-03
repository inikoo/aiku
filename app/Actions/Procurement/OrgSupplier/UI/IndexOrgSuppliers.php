<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Apr 2024 10:14:33 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplier\UI;

use App\Actions\OrgAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Http\Resources\Procurement\SupplierResource;
use App\InertiaTable\InertiaTable;
use App\Models\Procurement\OrgSupplier;
use App\Models\SupplyChain\Agent;
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
    /**
     * @var array|array[]
     */
    private array $elementGroups;

    protected function getSupplierElementGroups(Organisation|Agent $parent): void
    {
        $this->elementGroups =
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

    public function handle(Agent|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('suppliers.code', 'ILIKE', "$value%")
                    ->orWhere('suppliers.name', 'ILIKE', "%$value%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }


        $queryBuilder = QueryBuilder::for(OrgSupplier::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        return $queryBuilder
            ->defaultSort('organisations.code')
            ->leftJoin('organisations', 'organisation_id', 'organisations.id')
            ->leftJoin('suppliers', 'suppliers.id', 'org_suppliers.agent_id')
            ->leftJoin('agent_stats', 'agent_stats.agent_id', 'suppliers.id')
            ->where('org_suppliers.organisation_id', $this->organisation->id)
            ->select(['suppliers.slug', 'suppliers.name', 'organisations.code as org_code', 'organisations.name as org_name', 'organisations.slug as org_slug', 'organisations.location as org_location', 'agent_stats.number_suppliers', 'agent_stats.number_purchase_orders', 'agent_stats.number_supplier_products'])
            ->allowedSorts(['code', 'name', 'agent_name', 'supplier_locations', 'number_supplier_products', 'number_purchase_orders'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            foreach ($this->elementGroups as $key => $elementGroup) {
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
                        'title'       => __('no suppliers'),
                        'description' => $this->canEdit ? __('Get started by creating a new supplier.') : null,
                        'count'       => $this->organisation->inventoryStats->number_warehouse_areas,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new supplier'),
                            'label'   => __('supplier'),
                            'route'   => [
                                'name'       => 'grp.procurement.suppliers.create',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ]
                        ] : null
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

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('procurement.view')
            );
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);
        $this->getSupplierElementGroups($this->organisation);

        return $this->handle($this->organisation);
    }

    public function inAgent(Organisation $organisation, Agent $agent, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);
        $this->getSupplierElementGroups($agent);

        return $this->handle($agent);
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
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => __('suppliers'),
                'pageHead'    => [
                    'title' => __('suppliers'),
                ],
                'data'        => SupplierResource::collection($suppliers),


            ]
        )->table($this->tableStructure());
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return
            array_merge(
                ProcurementDashboard::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.suppliers.index',
                                'parameters' => [$this->organisation->id]
                            ],
                            'label' => __('suppliers'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            );
    }
}
