<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 09 May 2023 09:25:51 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\PurchaseOrder\UI;

use App\Actions\OrgAction;
use App\Actions\Procurement\OrgAgent\UI\ShowOrgAgent;
use App\Actions\Procurement\OrgAgent\WithOrgAgentSubNavigation;
use App\Actions\Procurement\OrgPartner\UI\ShowOrgPartner;
use App\Actions\Procurement\OrgPartner\WithOrgPartnerSubNavigation;
use App\Actions\Procurement\UI\ShowProcurementDashboard;
use App\Http\Resources\Procurement\PurchaseOrdersResource;
use App\InertiaTable\InertiaTable;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgPartner;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexPurchaseOrders extends OrgAction
{
    use WithOrgAgentSubNavigation;
    use WithOrgPartnerSubNavigation;
    private Organisation|OrgAgent|OrgSupplier|OrgPartner $parent;

    public function handle(Organisation|OrgAgent|OrgSupplier|OrgPartner $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('purchase_orders.reference', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(PurchaseOrder::class);
        if (class_basename($parent) == 'OrgAgent') {
            $query->where('purchase_orders.parent_type', 'OrgAgent')->where('purchase_orders.parent_id', $parent->id);
            $query->with('parent');
        } elseif (class_basename($parent) == 'OrgSupplier') {
            $query->where('purchase_orders.parent_type', 'OrgSupplier')->where('purchase_orders.parent_id', $parent->id);
            $query->with('parent');
        } elseif (class_basename($parent) == 'OrgPartner') {
            $query->where('purchase_orders.parent_type', 'OrgPartner')->where('purchase_orders.parent_id', $parent->id);
            $query->with('parent');
        } else {
            $query->where('purchase_orders.organisation_id', $parent->id);
            $query->with('parent');
        }

        return $query->defaultSort('purchase_orders.reference')

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
                ->column(key: 'state', label: __('state'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'parent_name', label: __('supplier/agents'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'date', label: __('date Created'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'items', label: __('items'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'amount', label: __('amount'), canBeHidden: false, sortable: true, searchable: true, type: 'currency')
                ->defaultSort('reference');
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

    public function maya(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->maya   = true;
        $this->initialisation($organisation, $request);

        return $this->handle(parent: $organisation);
    }

    public function inOrgAgent(Organisation $organisation, OrgAgent  $orgAgent, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $orgAgent;
        $this->initialisation($organisation, $request);

        return $this->handle($orgAgent);
    }

    public function inOrgPartner(Organisation $organisation, OrgPartner $orgPartner, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $orgPartner;
        $this->initialisation($organisation, $request);

        return $this->handle($orgPartner);
    }

    public function inOrgSupplier(Organisation $organisation, OrgSupplier  $orgSupplier, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $orgSupplier;
        $this->initialisation($organisation, $request);

        return $this->handle($orgSupplier);
    }


    public function jsonResponse(LengthAwarePaginator $purchaseOrders): AnonymousResourceCollection
    {
        return PurchaseOrdersResource::collection($purchaseOrders);
    }


    public function htmlResponse(LengthAwarePaginator $purchaseOrders, ActionRequest $request): Response
    {
        // dd($purchaseOrders);
        $subNavigation = null;
        $actions = [];
        if ($this->parent instanceof OrgAgent) {
            $subNavigation = $this->getOrgAgentNavigation($this->parent);
            $actions =
            [
                [
                    'type'  => 'button',
                    'style' => 'create',
                    'route' => [
                        'name'       => 'grp.models.org-agent.purchase-order.store',
                        'parameters' => [
                            'orgAgent' => $this->parent->id
                        ],
                        'method'     => 'post'
                    ],
                    'label' => __('purchase order')
                ]
            ];
        } elseif ($this->parent instanceof OrgPartner) {
            $subNavigation = $this->getOrgPartnerNavigation($this->parent);
            $actions = 
            [
                [
                    'type'  => 'button',
                    'style' => 'create',
                    'route' => [
                        'name'       => 'grp.models.org-partner.purchase-order.store',
                        'parameters' => [
                            'orgPartner' => $this->parent->id
                        ],
                        'method'     => 'post'
                    ],
                    'label' => __('purchase order')
                ]
            ];
        } 
        // dd($this->parent);
        return Inertia::render(
            'Procurement/PurchaseOrders',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), $request->route()->originalParameters()),
                'title'       => __('purchase orders'),
                'pageHead'    => [
                    'model'   => __('Procurement'),
                    'icon'    => ['fal', 'fa-clipboard-list'],
                    'title'   => __('purchase orders'),
                    'subNavigation' => $subNavigation,
                    'actions' => $actions
                ],
                'data'        => PurchaseOrdersResource::collection($purchaseOrders),
            ]
        )->table($this->tableStructure());
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return match ($routeName) {
            'grp.org.procurement.purchase_orders.index' => array_merge(
                ShowProcurementDashboard::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.purchase_orders.index',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Purchase Orders'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            ),
            'grp.org.procurement.org_agents.show.purchase-orders.index' => array_merge(
                ShowOrgAgent::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.org_agents.show.purchase-orders.index',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Purchase Orders'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            ),
            'grp.org.procurement.org_partners.show.purchase-orders.index' => array_merge(
                ShowOrgPartner::make()->getBreadcrumbs($this->parent, $routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.org_partners.show.purchase-orders.index',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Purchase Orders'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            )
        };
    }
}
