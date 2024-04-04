<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 13:52:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\OrgAgent\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\InertiaAction;
use App\Actions\Procurement\PurchaseOrder\UI\IndexPurchaseOrders;
use App\Actions\Procurement\SupplierProduct\UI\IndexSupplierProducts;
use App\Actions\Procurement\SupplierPurchaseOrder\UI\IndexSupplierPurchaseOrders;
use App\Actions\SupplyChain\Supplier\UI\IndexSuppliers;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\AgentOrganisationTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Procurement\AgentResource;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Http\Resources\Procurement\SupplierProductResource;
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\SupplyChain\Agent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowOrgAgent extends InertiaAction
{
    public function handle(Agent $agent): Agent
    {
        return $agent;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo('procurement.agents.edit');
        $this->canDelete = $request->user()->hasPermissionTo('procurement.agents.edit');

        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(Agent $agent, ActionRequest $request): RedirectResponse|Agent
    {
        if ($agent->trashed()) {
            return Redirect::route($request->route()->getName(), $request->route()->originalParameters());
        }
        $this->initialisation($request)->withTab(AgentOrganisationTabsEnum::values());

        return $this->handle($agent);
    }

    public function htmlResponse(Agent $agent, ActionRequest $request): Response
    {
        return Inertia::render(
            'Procurement/OrgAgent',
            [
                'title'       => __('agent'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($agent, $request),
                    'next'     => $this->getNext($agent, $request),
                ],
                'pageHead'    => [
                    'icon'          =>
                        [
                            'icon'  => ['fal', 'people-arrows'],
                            'title' => __('agent')
                        ],
                    'title'         => $agent->name,
                    'create_direct' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'grp.models.agent.purchase-order.store',
                            'parameters' => array_values($request->route()->originalParameters())
                        ],
                        'label' => __('purchase order')
                    ] : false,
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                        $this->canDelete ? [
                            'type'  => 'button',
                            'style' => 'delete',
                            'route' => [
                                'name'       => 'grp.procurement.agents.remove',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                    ],

                    'meta'          => [
                        [
                            'name'     => trans_choice('supplier|suppliers', $agent->stats->number_suppliers),
                            'number'   => $agent->stats->number_suppliers,
                            'href'     => [
                                'grp.procurement.agents.show.suppliers.index',
                                $agent->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-person-dolly',
                                'tooltip' => __('suppliers')
                            ]
                        ],
                        [
                            'name'     => trans_choice('product|products', $agent->stats->number_supplier_products),
                            'number'   => $agent->stats->number_supplier_products,
                            'href'     => [
                                'grp.procurement.agents.show.suppliers.index',
                                $agent->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-box-usd',
                                'tooltip' => __('products')
                            ]
                        ]
                    ]

                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => AgentOrganisationTabsEnum::navigation()
                ],

                AgentOrganisationTabsEnum::SHOWCASE->value => $this->tab == AgentOrganisationTabsEnum::SHOWCASE->value ?
                    fn () => GetAgentShowcase::run($agent)
                    : Inertia::lazy(fn () => GetAgentShowcase::run($agent)),


                AgentOrganisationTabsEnum::PURCHASE_ORDERS->value   => $this->tab == AgentOrganisationTabsEnum::PURCHASE_ORDERS->value
                    ?
                    fn () => PurchaseOrderResource::collection(
                        IndexSupplierPurchaseOrders::run(
                            parent: $agent,
                            prefix: 'purchase_orders'
                        )
                    )
                    : Inertia::lazy(fn () => PurchaseOrderResource::collection(
                        IndexSupplierPurchaseOrders::run(
                            parent: $agent,
                            prefix: 'purchase_orders'
                        )
                    )),
                AgentOrganisationTabsEnum::SUPPLIER_PRODUCTS->value => $this->tab == AgentOrganisationTabsEnum::SUPPLIER_PRODUCTS->value
                    ?
                    fn () => SupplierProductResource::collection(
                        IndexSupplierProducts::run(
                            parent: $agent,
                            prefix: 'supplier_products'
                        )
                    )
                    : Inertia::lazy(fn () => SupplierProductResource::collection(
                        IndexSupplierProducts::run(
                            parent: $agent,
                            prefix: 'supplier_products'
                        )
                    )),
                AgentOrganisationTabsEnum::SUPPLIERS->value         => $this->tab == AgentOrganisationTabsEnum::SUPPLIERS->value
                    ?
                    fn () => SupplierResource::collection(
                        IndexSuppliers::run(
                            parent: $agent,
                            prefix: 'suppliers'
                        )
                    )
                    : Inertia::lazy(fn () => SupplierResource::collection(
                        IndexSuppliers::run(
                            parent: $agent,
                            prefix: 'suppliers'
                        )
                    )),

                AgentOrganisationTabsEnum::HISTORY->value => $this->tab == AgentOrganisationTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($agent))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($agent)))
            ]
        )->table(
            IndexPurchaseOrders::make()->tableStructure(
                /* modelOperations: [
                    'createLink' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'grp.procurement.agents.show.purchase-orders.create',
                            'parameters' => array_values([$agent->slug])
                        ],
                        'label' => __('purchase_orders')
                    ] : false
                ],
                prefix: 'purchase_orders' */
            )
        )->table(
            IndexSupplierProducts::make()->tableStructure(
                /* modelOperations: [
                    'createLink' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'grp.procurement.agents.show.supplier-products-orders.create',
                            'parameters' => array_values([$agent->slug])
                        ],
                        'label' => __('supplier products')
                    ] : false
                ],
                prefix: 'supplier_products' */
            )
        )->table(
            IndexSuppliers::make()->tableStructure(
                /* modelOperations: [
                     'createLink' => $this->canEdit ? [
                         'route' => [
                             'name'       => 'grp.procurement.agents.show.suppliers.create',
                             'parameters' => array_values([$agent->slug])
                         ],
                         'label' => __('suppliers')
                     ] : false
                 ],
                 prefix: 'suppliers' */
            )
        )->table(IndexHistory::make()->tableStructure());
    }


    public function jsonResponse(Agent $agent): AgentResource
    {
        return new AgentResource($agent);
    }

    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        return array_merge(
            (new ProcurementDashboard())->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'grp.procurement.agents.index',
                            ],
                            'label' => __('agent')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.procurement.agents.show',
                                'parameters' => [$routeParameters['agent']->slug]
                            ],
                            'label' => $routeParameters['agent']->code,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(Agent $agent, ActionRequest $request): ?array
    {
        $previous = Agent::where('code', '<', $agent->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Agent $agent, ActionRequest $request): ?array
    {
        $next = Agent::where('code', '>', $agent->code)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Agent $agent, string $routeName): ?array
    {
        if (!$agent) {
            return null;
        }

        return match ($routeName) {
            'grp.procurement.agents.show' => [
                'label' => $agent->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'agent' => $agent->slug
                    ]

                ]
            ]
        };
    }

}
