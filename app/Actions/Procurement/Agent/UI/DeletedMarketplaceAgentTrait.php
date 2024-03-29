<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 16:20:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent\UI;

use App\Actions\Procurement\Marketplace\Supplier\UI\IndexMarketplaceSuppliers;
use App\Actions\Procurement\Marketplace\SupplierProduct\UI\IndexMarketplaceSupplierProducts;
use App\Enums\UI\MarketplaceAgentTabsEnum;
use App\Http\Resources\Procurement\MarketplaceSupplierProductResource;
use App\Http\Resources\Procurement\MarketplaceSupplierResource;
use App\Models\SupplyChain\Agent;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

trait DeletedMarketplaceAgentTrait
{
    public function deletedHtmlResponse(Agent $agent, ActionRequest $request): Response
    {

        return Inertia::render(
            'Procurement/DeletedMarketplaceAgent',
            [
                'title'                                    => __("agent"),
                'breadcrumbs'                              => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'navigation'    => [
                    'previous'  => $this->getPrevious($agent, $request),
                    'next'      => $this->getNext($agent, $request),
                ],
                'pageHead'                                 => [
                    'icon'          =>
                        [
                            'icon'  => ['fal', 'people-arrows'],
                            'title' => __('agent')
                        ],
                    'title'  => $agent->name,



                ],
                'tabs'                                     => [
                    'current'    => $this->tab,
                    'navigation' => MarketplaceAgentTabsEnum::navigation()
                ],
                MarketplaceAgentTabsEnum::SHOWCASE->value => $this->tab == MarketplaceAgentTabsEnum::SHOWCASE->value ?
                    fn () => GetMarketplaceAgentShowcase::run($agent)
                    : Inertia::lazy(fn () => GetMarketplaceAgentShowcase::run($agent)),

                MarketplaceAgentTabsEnum::SUPPLIERS->value => $this->tab == MarketplaceAgentTabsEnum::SUPPLIERS->value ?
                    fn () => MarketplaceSupplierResource::collection(
                        IndexMarketplaceSuppliers::run(
                            parent: $agent,
                            prefix: 'suppliers'
                        )
                    )
                    : Inertia::lazy(fn () => MarketplaceSupplierResource::collection(
                        IndexMarketplaceSuppliers::run(
                            parent: $agent,
                            prefix: 'suppliers'
                        )
                    )),

                MarketplaceAgentTabsEnum::SUPPLIER_PRODUCTS->value => $this->tab == MarketplaceAgentTabsEnum::SUPPLIER_PRODUCTS->value ?
                    fn () => MarketplaceSupplierProductResource::collection(IndexMarketplaceSupplierProducts::run($agent))
                    : Inertia::lazy(fn () => MarketplaceSupplierProductResource::collection(IndexMarketplaceSupplierProducts::run($agent))),

            ]
        )->table(
            IndexMarketplaceSuppliers::make()->tableStructure(
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
            ->table(IndexMarketplaceSupplierProducts::make()->tableStructure(
                /* modelOperations: [
                    'createLink' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'grp.org.procurement.marketplace.agents.show.supplier-products.create',
                            'parameters' => array_values($request->route()->originalParameters())
                        ],
                        'label' => __('product')
                    ] : false,
                ],
                prefix: 'supplier_products' */
            ));
    }

}
