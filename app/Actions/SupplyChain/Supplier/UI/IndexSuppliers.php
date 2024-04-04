<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Apr 2024 10:14:33 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Supplier\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Http\Resources\Procurement\SupplierResource;
use App\InertiaTable\InertiaTable;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexSuppliers extends InertiaAction
{
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

        $parent->suppliers()->count();


        $queryBuilder = QueryBuilder::for(Supplier::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
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
            ->leftJoin('supplier_stats', 'supplier_stats.supplier_id', 'suppliers.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Agent') {
                    //                    $query->where('suppliers.owner_type', 'supplier');
                    $query->where('suppliers.owner_id', $parent->id);
                    $query->leftJoin('agents', 'suppliers.owner_id', 'agents.id');
                    $query->addSelect('agents.slug as agent_slug');
                    $query->addSelect('agents.name as agent_name');
                } else {
                    $query ->leftJoin('org_suppliers', 'suppliers.id', 'org_suppliers.supplier_id');
                    $query->where('suppliers.owner_type', 'Organisation');
                    $query->where('org_suppliers.organisation_id', app('currentTenant')->id);

                }
            })
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
                        'count'       => app('currentTenant')->inventoryStats->number_warehouse_areas,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new supplier'),
                            'label'   => __('supplier'),
                            'route'   => [
                                'name'       => 'grp.procurement.suppliers.create',
                                'parameters' => array_values($request->route()->originalParameters())
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
        $this->canEdit = $request->user()->hasPermissionTo('procurement.suppliers.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('procurement.view')
            );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        $this->getSupplierElementGroups(app('currentTenant'));
        return $this->handle(app('currentTenant'));
    }

    public function inAgent(Agent $agent, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        $this->getSupplierElementGroups($agent);
        return $this->handle($agent);
    }

    public function jsonResponse(LengthAwarePaginator $suppliers): AnonymousResourceCollection
    {
        return SupplierResource::collection($suppliers);
    }


    public function htmlResponse(LengthAwarePaginator $suppliers): Response
    {
        return Inertia::render(
            'Procurement/Suppliers',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('suppliers'),
                'pageHead'    => [
                    'title' => __('suppliers'),
                ],
                'data'        => SupplierResource::collection($suppliers),


            ]
        )->table($this->tableStructure());
    }

    public function getBreadcrumbs(): array
    {
        return
            array_merge(
                ProcurementDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'grp.procurement.suppliers.index'
                            ],
                            'label' => __('suppliers'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            );
    }
}
