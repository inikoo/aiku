<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Mar 2023 15:55:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierProduct\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Http\Resources\Procurement\SupplierProductResource;
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowSupplierProduct extends InertiaAction
{
    public $acReq;
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
        $this->acReq = $request;
        return $this->handle($supplierProduct);
    }

    public function htmlResponse(SupplierProduct $supplierProduct): Response
    {
        return Inertia::render(
            'Procurement/SupplierProduct',
            [
                'title'       => __('supplier product'),
                'breadcrumbs' => $this->getBreadcrumbs($supplierProduct),
                'navigation'                            => [
                    'previous' => $this->getPrevious($supplierProduct, $this->acReq),
                    'next'     => $this->getNext($supplierProduct, $this->acReq),
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
                'supplier'    => new SupplierProductResource($supplierProduct)
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
                    'suffix' => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(SupplierProduct $supplierProduct, ActionRequest $request): ?array
    {
        $previous = SupplierProduct::where('code', '<', $supplierProduct->code)->orderBy('code', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(SupplierProduct $supplierProduct, ActionRequest $request): ?array
    {
        $next = SupplierProduct::where('code', '>', $supplierProduct->code)->orderBy('code')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?SupplierProduct $supplierProduct, string $routeName): ?array
    {
        if(!$supplierProduct) {
            return null;
        }

        return match ($routeName) {
            'procurement.supplier-products.show'=> [
                'label'=> $supplierProduct->code,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'supplierProduct'=> $supplierProduct->slug
                    ]

                ]
            ]
        };
    }
}
