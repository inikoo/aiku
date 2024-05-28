<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 20:10:25 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Agent\UI;

use App\Actions\GrpAction;
use App\Actions\Procurement\Marketplace\SupplierProduct\UI\IndexMarketplaceSupplierProducts;
use App\Actions\SupplyChain\Supplier\UI\IndexSuppliers;
use App\Actions\SupplyChain\UI\ShowSupplyChainDashboard;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Enums\UI\SupplyChain\AgentTabsEnum;
use App\Http\Resources\Procurement\AgentResource;
use App\Http\Resources\Procurement\MarketplaceSupplierProductResource;
use App\Http\Resources\Procurement\MarketplaceSupplierResource;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowAgent extends GrpAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('supply-chain.edit');
        return $request->user()->hasPermissionTo('supply-chain.view');
    }

    public function handle(Agent $agent): Agent
    {
        return $agent;
    }



    public function asController(Agent $agent, ActionRequest $request): Agent
    {
        $this->initialisation(app('group'), $request)->withTab(AgentTabsEnum::values());

        return $this->handle($agent);
    }


    public function htmlResponse(Agent $agent, ActionRequest $request): Response
    {
        if ($agent->trashed()) {
            return $this->deletedHtmlResponse($agent, $request);
        }

        return Inertia::render(
            'SupplyChain/Agent',
            [
                'title'                                   => __("agent"),
                'breadcrumbs'                             => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'navigation'                              => [
                    'previous' => $this->getPrevious($agent, $request),
                    'next'     => $this->getNext($agent, $request),
                ],
                'pageHead'                                => [
                    'icon'   =>
                        [
                            'icon'  => ['fal', 'people-arrows'],
                            'title' => __('agent')
                        ],
                    'title'   => $agent->organisation->name,
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
                                'name'       => 'grp.org.procurement.marketplace.agents.remove',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'create',
                            'route' => [
                                'name'       => 'grp.org.procurement.marketplace.agents.show.suppliers.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                            'label' => __('supplier')
                        ] : false,
                    ],
                    /*
                    'meta'   => [
                        [
                            'href'     => ['grp.org.procurement.marketplace.agents.show.suppliers.index', $agent->slug],
                            'name'     => trans_choice('supplier|suppliers', $agent->stats->number_suppliers),
                            'number'   => $agent->stats->number_suppliers,
                            'leftIcon' => [
                                'icon'    => 'fal fa-person-dolly',
                                'tooltip' => __('Suppliers'),
                            ],
                        ],
                        [
                            'href'     => ['grp.org.procurement.marketplace.agents.show.supplier_products.index', $agent->slug],
                            'name'     => trans_choice('product|products', $agent->stats->number_supplier_products),
                            'number'   => $agent->stats->number_supplier_products,
                            'leftIcon' => [
                                'icon'    => 'fal fa-box-usd',
                                'tooltip' => __('products'),
                            ],
                        ]
                    ]
                    */

                ],
                'tabs'                                    => [
                    'current'    => $this->tab,
                    'navigation' => AgentTabsEnum::navigation()
                ],
                AgentTabsEnum::SHOWCASE->value => $this->tab == AgentTabsEnum::SHOWCASE->value ?
                    fn () => GetAgentShowcase::run($agent)
                    : Inertia::lazy(fn () => GetAgentShowcase::run($agent)),

                AgentTabsEnum::SUPPLIERS->value => $this->tab == AgentTabsEnum::SUPPLIERS->value
                    ?
                    fn () => MarketplaceSupplierResource::collection(
                        IndexSuppliers::run(
                            parent: $agent,
                            prefix: 'suppliers'
                        )
                    )
                    : Inertia::lazy(fn () => MarketplaceSupplierResource::collection(
                        IndexSuppliers::run(
                            parent: $agent,
                            prefix: 'suppliers'
                        )
                    )),

                AgentTabsEnum::SUPPLIER_PRODUCTS->value => $this->tab == AgentTabsEnum::SUPPLIER_PRODUCTS->value ?
                    fn () => MarketplaceSupplierProductResource::collection(IndexMarketplaceSupplierProducts::run($agent))
                    : Inertia::lazy(fn () => MarketplaceSupplierProductResource::collection(IndexMarketplaceSupplierProducts::run($agent))),

            ]
        )->table(
            IndexSuppliers::make()->tableStructure(
                parent:$agent
                /* modelOperations: [
                    'createLink' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'grp.org.procurement.marketplace.agents.show.suppliers.create',
                            'parameters' => array_values($request->route()->originalParameters())
                        ],
                        'label' => __('suppliers')
                    ] : false,
                ],
                prefix: 'suppliers' */
            )
        )
            ->table(
                IndexMarketplaceSupplierProducts::make()->tableStructure(
                    /* modelOperations: [
                        'createLink' => $this->canEdit ? [
                            'route' => [
                                'name'       => 'grp.org.procurement.marketplace.agents.show.supplier_products.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                            'label' => __('product')
                        ] : false,
                    ],
                    prefix: 'supplier_products' */
                )
            );
    }


    public function jsonResponse(Agent $agent): AgentResource
    {
        return new AgentResource($agent);
    }

    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {

        $agent=Agent::where('slug', $routeParameters['agent'])->first();
        return array_merge(
            ShowSupplyChainDashboard::make()->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'grp.supply-chain.agents.index',
                            ],
                            'label' => __("agents"),
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.supply-chain.agents.show',
                                'parameters' => $routeParameters
                            ],
                            'label' => $agent->organisation->code,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(Agent $agent, ActionRequest $request): ?array
    {
        $previous = Organisation::where('group_id', $agent->group_id)->where('type', OrganisationTypeEnum::AGENT)->where('code', '<', $agent->organisation->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous?->agent, $request->route()->getName());
    }

    public function getNext(Agent $agent, ActionRequest $request): ?array
    {
        $next = Organisation::where('group_id', $agent->group_id)->where('type', OrganisationTypeEnum::AGENT)->where('code', '>', $agent->organisation->code)->orderBy('code')->first();

        return $this->getNavigation($next?->agent, $request->route()->getName());
    }

    private function getNavigation(?Agent $agent, string $routeName): ?array
    {
        if (!$agent) {
            return null;
        }

        return match ($routeName) {
            'grp.supply-chain.agents.show' => [
                'label' => $agent->organisation->name,
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
