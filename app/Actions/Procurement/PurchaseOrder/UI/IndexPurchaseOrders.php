<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 09 May 2023 09:25:51 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\PurchaseOrder\UI;

use App\Actions\OrgAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\InertiaTable\InertiaTable;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexPurchaseOrders extends OrgAction
{
    public function handle(Organisation|Agent|Supplier $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('purchase_orders.number', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(PurchaseOrder::class);

        if (class_basename($parent) == 'Agent') {
            $query->leftJoin('agents', 'agents.id', 'purchase_orders.parent_id');
            $query->where('parent_type', 'Agent')->where('purchase_orders.parent_id', $parent->id);
            $query->addSelect('agents.slug as agent_slug');
        } elseif (class_basename($parent) == 'Organisation') {
            $query->where('purchase_orders.organisation_id', $parent->id);

            $query->addSelect(['parent_id', 'parent_type']);
        } else {
            $query->where('parent_type', 'Supplier')->where('parent_id', $parent->id);
        }


        return $query->defaultSort('purchase_orders.number')
            ->select(['number', 'purchase_orders.slug', 'date'])
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
            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->column(key: 'number', label: __('number'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'parent', label: __('OrgAgent/Supplier'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'created_by', label: __('Created by'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'date', label: __('date'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'author', label: __('state'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'amount', label: __('amount'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('number');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation);
    }

    public function inAgent(Agent $agent): LengthAwarePaginator
    {
        $this->validateAttributes();

        return $this->handle($agent);
    }


    public function jsonResponse(LengthAwarePaginator $purchaseOrders): AnonymousResourceCollection
    {
        return PurchaseOrderResource::collection($purchaseOrders);
    }


    public function htmlResponse(LengthAwarePaginator $purchaseOrders, ActionRequest $request): Response
    {
        return Inertia::render(
            'Procurement/PurchaseOrders',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => __('purchase orders'),
                'pageHead'    => [
                    'title' => __('purchase orders'),
                ],
                'data'        => PurchaseOrderResource::collection($purchaseOrders),
            ]
        )->table($this->tableStructure());
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return
            array_merge(
                ProcurementDashboard::make()->getBreadcrumbs(Arr::only($routeParameters, 'organisation')),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.purchase-orders.index',
                                'parameters' => ['organisation' => $routeParameters['organisation']]

                            ],
                            'label' => __('purchase orders'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            );
    }
}
