<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 May 2023 20:15:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Marketplace\SupplierProduct\UI;

use App\Actions\InertiaAction;
use App\Actions\Procurement\Marketplace\Agent\UI\ShowMarketplaceAgent;
use App\Actions\Procurement\Marketplace\Supplier\UI\ShowMarketplaceSupplier;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\MarketplaceSupplierProductTabsEnum;
use App\Http\Resources\Procurement\MarketplaceSupplierProductResource;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowMarketplaceSupplierProduct extends InertiaAction
{
    public function handle(SupplierProduct $supplierProduct): SupplierProduct
    {
        return $supplierProduct;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.suppliers.edit');

        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(SupplierProduct $supplierProduct, ActionRequest $request): SupplierProduct
    {
        $this->initialisation($request)->withTab(MarketplaceSupplierProductTabsEnum::values());

        return $this->handle($supplierProduct);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inAgent(Agent $agent, SupplierProduct $supplierProduct, ActionRequest $request): SupplierProduct
    {
        $this->initialisation($request)->withTab(MarketplaceSupplierProductTabsEnum::values());

        return $this->handle($supplierProduct);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inSupplierInAgent(Agent $agent, Supplier $supplier, SupplierProduct $supplierProduct, ActionRequest $request): SupplierProduct
    {
        $this->initialisation($request)->withTab(MarketplaceSupplierProductTabsEnum::values());

        return $this->handle($supplierProduct);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inSupplier(Supplier $supplier, SupplierProduct $supplierProduct, ActionRequest $request): SupplierProduct
    {
        $this->initialisation($request)->withTab(MarketplaceSupplierProductTabsEnum::values());

        return $this->handle($supplierProduct);
    }

    public function htmlResponse(SupplierProduct $supplierProduct, ActionRequest $request): Response
    {
        return Inertia::render(
            'Procurement/MarketplaceSupplierProduct',
            [
                'title'       => __('supplier product'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'pageHead'    => [
                    'title' => $supplierProduct->name,

                    'edit' => $this->canEdit &&  $request->route()->getName()=='procurement.marketplace.agents.show.suppliers.show.supplier-products.edit' ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,

                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => MarketplaceSupplierProductTabsEnum::navigation()
                ],


                MarketplaceSupplierProductTabsEnum::SHOWCASE->value => $this->tab == MarketplaceSupplierProductTabsEnum::SHOWCASE->value ?
                    fn () => MarketplaceSupplierProductResource::make($supplierProduct)->getArray()
                    : Inertia::lazy(fn () => MarketplaceSupplierProductResource::make($supplierProduct)->getArray()),

            ]
        );
    }


    public function jsonResponse(SupplierProduct $supplierProduct): MarketplaceSupplierProductResource
    {
        return new MarketplaceSupplierProductResource($supplierProduct);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (SupplierProduct $supplierProduct, array $routeParameters, string $suffix) {
            return [
                [

                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('supplier products')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $supplierProduct->code,
                        ],

                    ],
                    'suffix'         => $suffix

                ],
            ];
        };

        return match ($routeName) {
            'procurement.marketplace.suppliers.show.supplier-products.show' => array_merge(
                (new ShowMarketplaceSupplier())->getBreadcrumbs(
                    'procurement.marketplace.suppliers.show',
                    ['supplier' => $routeParameters['supplier']]
                ),
                $headCrumb(
                    $routeParameters['supplierProduct'],
                    [
                        'index' => [
                            'name'       => 'procurement.marketplace.suppliers.show.supplier-products.index',
                            'parameters' => [
                                $routeParameters['supplier']->slug
                            ]
                        ],
                        'model' => [
                            'name'       => 'procurement.marketplace.suppliers.show.supplier-products.show',
                            'parameters' => [
                                $routeParameters['supplier']->slug,
                                $routeParameters['supplierProduct']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),


            'procurement.marketplace.supplier-products.show' => array_merge(
                (new ProcurementDashboard())->getBreadcrumbs(),
                $headCrumb(
                    $routeParameters['supplierProduct'],
                    [
                        'index' => [
                            'name'       => 'procurement.marketplace.supplier-products.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'procurement.marketplace.supplier-products.show',
                            'parameters' => [$routeParameters['supplierProduct']->slug]
                        ]
                    ],
                    $suffix
                )
            ),
            'procurement.marketplace.agents.show.supplier-products.show' => array_merge(
                (new ShowMarketplaceAgent())->getBreadcrumbs(
                    ['agent' => $routeParameters['agent']]
                ),
                $headCrumb(
                    $routeParameters['supplierProduct'],
                    [
                        'index' => [
                            'name'       => 'procurement.marketplace.agents.show.supplier-products.index',
                            'parameters' => [
                                $routeParameters['agent']->slug
                            ]
                        ],
                        'model' => [
                            'name'       => 'procurement.marketplace.agents.show.supplier-products.show',
                            'parameters' => [
                                $routeParameters['agent']->slug,
                                $routeParameters['supplierProduct']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),

            default => []
        };
    }
}
