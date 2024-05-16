<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 13:52:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\OrgAgent\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\InertiaAction;
use App\Actions\Procurement\OrgSupplier\UI\IndexOrgSuppliers;
use App\Actions\Procurement\PurchaseOrder\UI\IndexPurchaseOrders;
use App\Actions\Procurement\SupplierProduct\UI\IndexSupplierProducts;
use App\Actions\SupplyChain\Supplier\UI\IndexSuppliers;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\Procurement\OrgAgentTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Procurement\AgentResource;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Http\Resources\Procurement\SupplierProductResource;
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\Procurement\OrgAgent;
use App\Models\SupplyChain\Agent;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowOrgAgent extends InertiaAction
{
    public function handle(OrgAgent $orgAgent): OrgAgent
    {
        return $orgAgent;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo('procurement.agents.edit');
        $this->canDelete = $request->user()->hasPermissionTo('procurement.agents.edit');

        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(OrgAgent $orgAgent, ActionRequest $request): RedirectResponse|OrgAgent
    {

        $this->initialisation($request)->withTab(OrgAgentTabsEnum::values());

        return $this->handle($orgAgent);
    }

    public function htmlResponse(OrgAgent $orgAgent, ActionRequest $request): Response
    {
        return Inertia::render(
            'Procurement/OrgAgent',
            [
                'title'       => __('agent'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($orgAgent, $request),
                    'next'     => $this->getNext($orgAgent, $request),
                ],
                'pageHead'    => [
                    'icon'          =>
                        [
                            'icon'  => ['fal', 'people-arrows'],
                            'title' => __('agent')
                        ],
                    'title'         => $orgAgent->organisation->name,
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
                                'name'       => 'grp.org.procurement.agents.remove',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                    ],

                    'meta'          => [
                        [
                            'name'     => trans_choice('supplier|suppliers', $orgAgent->stats->number_suppliers),
                            'number'   => $orgAgent->stats->number_suppliers,
                            'href'     => [
                                'grp.org.procurement.agents.show.suppliers.index',
                                $orgAgent->organisation->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-person-dolly',
                                'tooltip' => __('suppliers')
                            ]
                        ],
                        [
                            'name'     => trans_choice('product|products', $orgAgent->stats->number_supplier_products),
                            'number'   => $orgAgent->stats->number_supplier_products,
                            'href'     => [
                                'grp.org.procurement.agents.show.suppliers.index',
                                $orgAgent->organisation->slug
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
                    'navigation' => OrgAgentTabsEnum::navigation()
                ],

                OrgAgentTabsEnum::SHOWCASE->value => $this->tab == OrgAgentTabsEnum::SHOWCASE->value ?
                    fn () => GetAgentShowcase::run($orgAgent)
                    : Inertia::lazy(fn () => GetAgentShowcase::run($orgAgent)),


                OrgAgentTabsEnum::PURCHASE_ORDERS->value   => $this->tab == OrgAgentTabsEnum::PURCHASE_ORDERS->value
                    ?
                    fn () => PurchaseOrderResource::collection(
                        IndexPurchaseOrders::run(
                            parent: $orgAgent,
                            prefix: 'purchase_orders'
                        )
                    )
                    : Inertia::lazy(fn () => PurchaseOrderResource::collection(
                        IndexPurchaseOrders::run(
                            parent: $orgAgent,
                            prefix: 'purchase_orders'
                        )
                    )),
                OrgAgentTabsEnum::SUPPLIER_PRODUCTS->value => $this->tab == OrgAgentTabsEnum::SUPPLIER_PRODUCTS->value
                    ?
                    fn () => SupplierProductResource::collection(
                        IndexSupplierProducts::run(
                            parent: $orgAgent,
                            prefix: 'supplier_products'
                        )
                    )
                    : Inertia::lazy(fn () => SupplierProductResource::collection(
                        IndexSupplierProducts::run(
                            parent: $orgAgent,
                            prefix: 'supplier_products'
                        )
                    )),
                OrgAgentTabsEnum::SUPPLIERS->value         => $this->tab == OrgAgentTabsEnum::SUPPLIERS->value
                    ?
                    fn () => SupplierResource::collection(
                        IndexSuppliers::run(
                            parent: $orgAgent,
                            prefix: 'suppliers'
                        )
                    )
                    : Inertia::lazy(fn () => SupplierResource::collection(
                        IndexSuppliers::run(
                            parent: $orgAgent,
                            prefix: 'suppliers'
                        )
                    )),

                OrgAgentTabsEnum::HISTORY->value => $this->tab == OrgAgentTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($orgAgent))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($orgAgent)))
            ]
        )->table(
            IndexPurchaseOrders::make()->tableStructure(
                /* modelOperations: [
                    'createLink' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'grp.org.procurement.agents.show.purchase-orders.create',
                            'parameters' => array_values([$orgAgent->slug])
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
                            'name'       => 'grp.org.procurement.agents.show.supplier-products-orders.create',
                            'parameters' => array_values([$orgAgent->slug])
                        ],
                        'label' => __('supplier products')
                    ] : false
                ],
                prefix: 'supplier_products' */
            )
        )->table(
            IndexOrgSuppliers::make()->tableStructure(
                $orgAgent
                /* modelOperations: [
                     'createLink' => $this->canEdit ? [
                         'route' => [
                             'name'       => 'grp.org.procurement.agents.show.suppliers.create',
                             'parameters' => array_values([$orgAgent->slug])
                         ],
                         'label' => __('suppliers')
                     ] : false
                 ],
                 prefix: 'suppliers' */
            )
        )->table(IndexHistory::make()->tableStructure('hst'));
    }


    public function jsonResponse(Agent $orgAgent): AgentResource
    {
        return new AgentResource($orgAgent);
    }

    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        return array_merge(
            (new ProcurementDashboard())->getBreadcrumbs($routeParameters),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'grp.org.procurement.agents.index',
                            ],
                            'label' => __('agent')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.agents.show',
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

    public function getPrevious(OrgAgent $orgAgent, ActionRequest $request): ?array
    {
        $previous = Agent::where('code', '<', $orgAgent->organisation->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(OrgAgent $orgAgent, ActionRequest $request): ?array
    {
        $next = Agent::where('code', '>', $orgAgent->organisation->code)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Agent $orgAgent, string $routeName): ?array
    {
        if (!$orgAgent) {
            return null;
        }

        return match ($routeName) {
            'grp.org.procurement.agents.show' => [
                'label' => $orgAgent->organisation->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'agent' => $orgAgent->slug
                    ]

                ]
            ]
        };
    }

}
