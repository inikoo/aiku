<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 May 2024 12:07:41 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Manufacturing\JobOrder\UI;

use App\Actions\OrgAction;
use App\Http\Resources\Manufacturing\JobOrderResource;
use App\Models\Manufacturing\JobOrder;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Inertia\Response;

class ShowJobOrder extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('supply-chain.edit');
        return $request->user()->hasPermissionTo('supply-chain.view');
    }

    public function handle(JobOrder $jobOrder): JobOrder
    {
        return $jobOrder;
    }



    public function asController(JobOrder $jobOrder, ActionRequest $request): JobOrder
    {
        $this->initialisation(app('group'), $request);

        return $this->handle($jobOrder);
    }


    public function htmlResponse(JobOrder $jobOrder, ActionRequest $request): Response
    {


        return Inertia::render(
            'SupplyChain/Agent',
            [
                'title'                                   => __("Job Order"),
                'breadcrumbs'                             => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'navigation'                              => [
                    'previous' => $this->getPrevious($jobOrder, $request),
                    'next'     => $this->getNext($jobOrder, $request),
                ],
                'pageHead'                                => [
                    'icon'   =>
                        [
                            'icon'  => ['fal', 'people-arrows'],
                            'title' => __('agent')
                        ],
                    'title'   => $jobOrder->organisation->name,
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


    public function jsonResponse(JobOrder $jobOrder): JobOrderResource
    {
        return new JobOrderResource($jobOrder);
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

    public function getPrevious(JobOrder $jobOrder, ActionRequest $request): ?array
    {
        $previous = Organisation::where('group_id', $jobOrder->group_id)->where('code', '<', $jobOrder->organisation->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous?->agent, $request->route()->getName());
    }

    public function getNext(JobOrder $jobOrder, ActionRequest $request): ?array
    {
        $next = Organisation::where('group_id', $jobOrder->group_id)->where('code', '>', $jobOrder->organisation->code)->orderBy('code')->first();

        return $this->getNavigation($next?->agent, $request->route()->getName());
    }

    private function getNavigation(?JobOrder $jobOrder, string $routeName): ?array
    {
        if (!$jobOrder) {
            return null;
        }

        return match ($routeName) {
            'grp.org.productions.show.job-order.show' => [
                'label' => $jobOrder->organisation->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'job-order' => $jobOrder->slug
                    ]

                ]
            ]
        };
    }
}
