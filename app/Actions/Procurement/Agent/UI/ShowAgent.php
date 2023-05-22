<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 13:52:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Agent\UI;

use App\Actions\InertiaAction;
use App\Actions\Procurement\Supplier\UI\IndexSuppliers;
use App\Actions\Procurement\SupplierProduct\UI\IndexSupplierProducts;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\AgentTabsEnum;
use App\Http\Resources\Procurement\AgentResource;
use App\Http\Resources\Procurement\SupplierProductResource;
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\Procurement\Agent;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowAgent extends InertiaAction
{
    public function handle(Agent $agent): Agent
    {
        return $agent;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.agents.edit');

        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(Agent $agent, ActionRequest $request): Agent
    {
        $this->initialisation($request)->withTab(AgentTabsEnum::values());
        return $this->handle($agent);
    }

    public function htmlResponse(Agent $agent, ActionRequest $request): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Procurement/Agent',
            [
                'title'       => __('agent'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'pageHead'    => [
                    'icon'  => 'fal fa-agent',
                    'title' => $agent->name,
                    /*
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                    */
                    'create_direct' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'models.agent.purchase-order.store',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label' => __('purchase order')
                    ] : false,
                    'meta'  => [
                        [
                            'name'     => trans_choice('supplier|suppliers', $agent->stats->number_suppliers),
                            'number'   => $agent->stats->number_suppliers,
                            'href'     => [
                                'procurement.agents.show.suppliers.index',
                                $agent->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-person-dolly',
                                'tooltip' => __('suppliers')
                            ]
                        ],
                        [
                            'name'     => trans_choice('product|products', $agent->stats->number_products),
                            'number'   => $agent->stats->number_products,
                            'href'     => [
                                'procurement.agents.show.suppliers.index',
                                $agent->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-parachute-box',
                                'tooltip' => __('products')
                            ]
                        ]
                    ]

                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => AgentTabsEnum::navigation()
                ],

                AgentTabsEnum::SHOWCASE->value => $this->tab == AgentTabsEnum::SHOWCASE->value ?
                    fn () => $agent
                    : Inertia::lazy(fn () => $agent),

                AgentTabsEnum::SUPPLIERS->value => $this->tab == AgentTabsEnum::SUPPLIERS->value ?
                    fn () => SupplierResource::collection(IndexSuppliers::run($agent))
                    : Inertia::lazy(fn () => SupplierResource::collection(IndexSuppliers::run($agent))),

                AgentTabsEnum::SUPPLIER_PRODUCTS->value => $this->tab == AgentTabsEnum::SUPPLIER_PRODUCTS->value ?
                    fn () => SupplierProductResource::collection(IndexSupplierProducts::run($agent))
                    : Inertia::lazy(fn () => SupplierProductResource::collection(IndexSupplierProducts::run($agent))),

            ]
        )->table(IndexSuppliers::make()->tableStructure($agent))
            ->table(IndexSupplierProducts::make()->tableStructure($agent));
    }


     public function jsonResponse(Agent $agent): AgentResource
     {
         return new AgentResource($agent);
     }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = null): array
    {
        return array_merge(
            (new ProcurementDashboard())->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'procurement.agents.index',
                            ],
                            'label' => __('agent')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'procurement.agents.show',
                                'parameters' => [$routeParameters['agent']->slug]
                            ],
                            'label' => $routeParameters['agent']->code,
                        ],
                    ],
                    'suffix' => $suffix,

                ],
            ]
        );
    }
}
