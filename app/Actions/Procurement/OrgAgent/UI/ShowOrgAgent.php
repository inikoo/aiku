<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 13:52:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\OrgAgent\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\OrgAction;
use App\Actions\Procurement\OrgSupplier\UI\IndexOrgSuppliers;
use App\Actions\Procurement\PurchaseOrder\UI\IndexPurchaseOrders;
use App\Actions\Procurement\SupplierProduct\UI\IndexSupplierProducts;
use App\Actions\Procurement\UI\ProcurementDashboard;
use App\Actions\SupplyChain\Supplier\UI\IndexSuppliers;
use App\Enums\UI\Procurement\OrgAgentTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Procurement\OrgSupplierProductsResource;
use App\Http\Resources\Procurement\OrgSuppliersResource;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Http\Resources\SupplyChain\AgentsResource;
use App\Models\Procurement\OrgAgent;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowOrgAgent extends OrgAction
{
    public function handle(OrgAgent $orgAgent): OrgAgent
    {
        return $orgAgent;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, OrgAgent $orgAgent, ActionRequest $request): RedirectResponse|OrgAgent
    {

        $this->initialisation($organisation, $request)->withTab(OrgAgentTabsEnum::values());

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
                    'title'         => $orgAgent->agent->organisation->name,
                    'create_direct' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'grp.models.org_agent.purchase-order.store',
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
                                'name'       => 'grp.org.procurement.org_agents.remove',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                    ],

                    'meta'          => [
                        [
                            'name'     => trans_choice('supplier|suppliers', $orgAgent->stats->number_suppliers),
                            'number'   => $orgAgent->stats->number_suppliers,
                            'href'     => [
                                'grp.org.procurement.org_agents.show.org_suppliers.index',
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
                                'grp.org.procurement.org_agents.show.org_suppliers.index',
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
                    fn () => GetOrgAgentShowcase::run($orgAgent)
                    : Inertia::lazy(fn () => GetOrgAgentShowcase::run($orgAgent)),


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
                OrgAgentTabsEnum::ORG_SUPPLIER_PRODUCTS->value => $this->tab == OrgAgentTabsEnum::ORG_SUPPLIER_PRODUCTS->value
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
                OrgAgentTabsEnum::ORG_SUPPLIERS->value         => $this->tab == OrgAgentTabsEnum::ORG_SUPPLIERS->value
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
                            'name'       => 'grp.org.procurement.org_agents.show.purchase_orders.create',
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
                            'name'       => 'grp.org.procurement.org_agents.show.supplier-products-orders.create',
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
                             'name'       => 'grp.org.procurement.org_agents.show.org_suppliers.create',
                             'parameters' => array_values([$orgAgent->slug])
                         ],
                         'label' => __('suppliers')
                     ] : false
                 ],
                 prefix: 'suppliers' */
            )
        )->table(IndexHistory::make()->tableStructure(prefix: OrgAgentTabsEnum::HISTORY->value));
    }


    public function jsonResponse(Agent $orgAgent): AgentsResource
    {
        return new AgentsResource($orgAgent);
    }

    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {

        $orgAgent=OrgAgent::where('slug', $routeParameters['orgAgent'])->first();

        return array_merge(
            (new ShowProcurementDashboard())->getBreadcrumbs($routeParameters),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.org_agents.index',
                                'parameters' => [
                                    $routeParameters['organisation'],]
                            ],
                            'label' => __('Agents')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.org_agents.show',
                                'parameters' => [
                                    $routeParameters['organisation'],
                                    $orgAgent->slug
                                ]
                            ],
                            'label' => $orgAgent->agent->code,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(OrgAgent $orgAgent, ActionRequest $request): ?array
    {
        $previous = OrgAgent::where('slug', '<', $orgAgent->slug)->orderBy('slug', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(OrgAgent $orgAgent, ActionRequest $request): ?array
    {
        $next = OrgAgent::where('slug', '>', $orgAgent->slug)->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?OrgAgent $orgAgent, string $routeName): ?array
    {
        if (!$orgAgent) {
            return null;
        }

        return match ($routeName) {
            'grp.org.procurement.org_agents.show' => [
                'label' => $orgAgent->organisation->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $orgAgent->organisation->slug,
                        'orgAgent'     => $orgAgent->slug
                    ]

                ]
            ]
        };
    }

}
