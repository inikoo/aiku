<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Mar 2023 15:55:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierProduct\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Procurement\SupplierProductResource;
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
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

    public function htmlResponse(SupplierProduct $supplierProduct): Response
    {
        return Inertia::render(
            'Procurement/Supplier',
            [
                'title'       => __('supplier'),
                'breadcrumbs' => $this->getBreadcrumbs(),
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


    #[Pure] public function jsonResponse(Supplier $supplier): SupplierResource
    {
        return new SupplierResource($supplier);
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
