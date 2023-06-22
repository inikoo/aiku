<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 14:14:56 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Supplier\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Models\Tenancy\Tenant;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexSuppliers extends InertiaAction
{
    protected function getSupplierElementGroups(Tenant|Agent $parent): void
    {
        $this->elementGroups =
            [
                'status' => [
                    'label'    => __('status'),
                    'elements' => [
                        'active'   => [__('active'), $parent->procurementStats->number_suppliers_type_supplier],
                        'archived' => [__('archived'), $parent->procurementStats->number_archived_suppliers_type_supplier]
                    ],

                    'engine' => function ($query, $elements) {
                        $query->whereIn('state', $elements);
                    }

                ],

            ];
    }

    public function handle(Agent|Tenant $parent, $prefix = null): LengthAwarePaginator
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
            /** @noinspection PhpUndefinedMethodInspection */
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }
        /** @noinspection PhpUndefinedMethodInspection */
        return $queryBuilder
            ->defaultSort('suppliers.code')
            ->select(['suppliers.code', 'suppliers.slug', 'suppliers.name', 'suppliers.location as supplier_locations', 'number_supplier_products', 'number_purchase_orders'])
            ->leftJoin('supplier_stats', 'supplier_stats.supplier_id', 'suppliers.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Agent') {
                    $query->where('suppliers.owner_type', 'Agent');
                    $query->where('suppliers.owner_id', $parent->id);
                    $query->leftJoin('agents', 'suppliers.owner_id', 'agents.id');
                    $query->addSelect('agents.slug as agent_slug');
                    $query->addSelect('agents.name as agent_name');

                } else {
                    $query ->leftJoin('supplier_tenant', 'suppliers.id', 'supplier_tenant.supplier_id');

                    $query->where('suppliers.type', 'supplier');
                    $query->where('supplier_tenant.tenant_id', app('currentTenant')->id);

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
        $this->canEdit = $request->user()->can('procurement.suppliers.edit');

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
                                'name' => 'procurement.suppliers.index'
                            ],
                            'label' => __('suppliers'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            );
    }
}
