<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Mar 2023 15:55:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierProduct\UI;

use App\Actions\InertiaAction;
use App\Actions\Procurement\Agent\UI\GetAgentShowcase;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\SupplierProductTabsEnum;
use App\Http\Resources\Procurement\SupplierProductResource;
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowSupplierProduct extends InertiaAction
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
        $this->initialisation($request);

        return $this->handle($supplierProduct);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inAgent(Agent $agent, SupplierProduct $supplierProduct, ActionRequest $request): SupplierProduct
    {
        $this->initialisation($request);

        return $this->handle($supplierProduct);
    }

    public function htmlResponse(SupplierProduct $supplierProduct, ActionRequest $request): Response
    {
        return Inertia::render(
            'Procurement/SupplierProduct',
            [
                'title'       => __('supplier product'),
                'breadcrumbs' => $this->getBreadcrumbs($supplierProduct),
                'navigation'  => [
                    'previous' => $this->getPrevious($supplierProduct, $request),
                    'next'     => $this->getNext($supplierProduct, $request),
                ],
                'pageHead'    => [
                    'title' => $supplierProduct->name,
                    /*
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                    */
                ],
                'supplier'    => new SupplierProductResource($supplierProduct),
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => SupplierProductTabsEnum::navigation()
                ],
                SupplierProductTabsEnum::SHOWCASE->value => $this->tab == SupplierProductTabsEnum::SHOWCASE->value ?
                    fn () => GetAgentShowcase::run($supplierProduct)
                    : Inertia::lazy(fn () => GetAgentShowcase::run($supplierProduct)),


            ]
        );
    }


    public function jsonResponse(Supplier $supplier): SupplierResource
    {
        return new SupplierResource($supplier);
    }

    public function getBreadcrumbs(SupplierProduct $supplierProduct, $suffix = null): array
    {
        return array_merge(
            (new ProcurementDashboard())->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'procurement.supplier-products.index',
                            ],
                            'label' => __('purchaseOrder')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'procurement.supplier-products.show',
                                'parameters' => [$supplierProduct->code]
                            ],
                            'label' => $supplierProduct->code,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(SupplierProduct $supplierProduct, ActionRequest $request): ?array
    {
        $query = SupplierProduct::where('code', '<', $supplierProduct->code);

        $query = match ($request->route()->getName()) {
            'procurement.agents.show.supplier-products.show' => $query->where('supplier_products.agent_id', $request->route()->parameters['agent']->id),
            'procurement.agents.show.show.supplier.supplier-products.show',
            'procurement.supplier.supplier-products.show' => $query->where('supplier_products.supplier_id', $request->route()->parameters['supplier']->id),

            default => $query
        };

        $previous = $query->orderBy('code', 'desc')->first();


        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(SupplierProduct $supplierProduct, ActionRequest $request): ?array
    {
        $query = SupplierProduct::where('code', '>', $supplierProduct->code);

        $query = match ($request->route()->getName()) {
            'procurement.agents.show.supplier-products.show' => $query->where('supplier_products.agent_id', $request->route()->parameters['agent']->id),
            'procurement.agents.show.show.supplier.supplier-products.show',
            'procurement.supplier.supplier-products.show' => $query->where('supplier_products.supplier_id', $request->route()->parameters['supplier']->id),

            default => $query
        };

        $next = $query->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?SupplierProduct $supplierProduct, string $routeName): ?array
    {
        if (!$supplierProduct) {
            return null;
        }


        return match ($routeName) {
            'procurement.supplier-products.show' => [
                'label' => $supplierProduct->code,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'supplierProduct' => $supplierProduct->slug
                    ]

                ]
            ],
            'procurement.agents.show.supplier-products.show' => [
                'label' => $supplierProduct->code,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'agent'           => $supplierProduct->agent->slug,
                        'supplierProduct' => $supplierProduct->slug
                    ]

                ]
            ]
        };
    }
}
