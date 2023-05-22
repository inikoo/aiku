<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 13:52:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Marketplace\Agent\UI;

use App\Actions\InertiaAction;
use App\Actions\Procurement\Marketplace\Supplier\UI\IndexMarketplaceSuppliers;
use App\Actions\Procurement\SupplierProduct\UI\IndexSupplierProducts;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\MarketplaceAgentTabsEnum;
use App\Http\Resources\Procurement\AgentResource;
use App\Http\Resources\Procurement\MarketplaceSupplierResource;
use App\Http\Resources\Procurement\SupplierProductResource;
use App\Models\Procurement\Agent;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowMarketplaceAgent extends InertiaAction
{
    public function handle(Agent $agent): Agent
    {
        return $agent;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.edit');

        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(Agent $agent, ActionRequest $request): Agent
    {
        $this->initialisation($request)->withTab(MarketplaceAgentTabsEnum::values());
        return $this->handle($agent);

    }

    public function htmlResponse(Agent $agent, ActionRequest $request): Response
    {
        return Inertia::render(
            'Procurement/MarketplaceAgent',
            [
                'title'       => __('marketplace agent'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'pageHead'    => [
                    'icon'  => 'fal people-arrows',
                    'title' => $agent->name,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                    'create'  => $this->canEdit ? [
                        'route' => [
                            'name'       => 'procurement.marketplace-agents.show.suppliers.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('supplier')
                    ] : false,

                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => MarketplaceAgentTabsEnum::navigation()
                ],
                MarketplaceAgentTabsEnum::SUPPLIERS->value => $this->tab == MarketplaceAgentTabsEnum::SUPPLIERS->value ?
                    fn () => MarketplaceSupplierResource::collection(IndexMarketplaceSuppliers::run($agent))
                    : Inertia::lazy(fn () => MarketplaceSupplierResource::collection(IndexMarketplaceSuppliers::run($agent))),

                MarketplaceAgentTabsEnum::SUPPLIER_PRODUCTS->value => $this->tab == MarketplaceAgentTabsEnum::SUPPLIER_PRODUCTS->value ?
                    fn () => SupplierProductResource::collection(IndexSupplierProducts::run($agent))
                    : Inertia::lazy(fn () => SupplierProductResource::collection(IndexSupplierProducts::run($agent))),

            ]
        )->table(IndexMarketplaceSuppliers::make()->tableStructure($agent))
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
                                'name' => 'procurement.marketplace-agents.index',
                            ],
                            'label' => __("agent's marketplace"),
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'procurement.marketplace-agents.show',
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
