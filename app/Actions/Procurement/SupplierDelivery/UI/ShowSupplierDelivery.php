<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 14:15:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\SupplierDelivery\UI;

use App\Actions\InertiaAction;
use App\Actions\Procurement\Supplier\UI\HasUISupplier;
use App\Actions\Procurement\SupplierProduct\UI\IndexSupplierProducts;
use App\Enums\UI\SupplierTabsEnum;
use App\Http\Resources\Procurement\SupplierProductResource;
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\Procurement\Supplier;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Supplier $supplier
 */
class ShowSupplierDelivery extends InertiaAction
{
    use HasUISupplier;
    public function handle(Supplier $supplier): Supplier
    {
        return $supplier;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.suppliers.edit');

        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(Supplier $supplier, ActionRequest $request): Supplier
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request)->withTab(SupplierTabsEnum::values());
        return $this->handle($supplier);
    }

    public function htmlResponse(Supplier $supplier): Response
    {
        return Inertia::render(
            'Procurement/Supplier',
            [
                'title'       => __('supplier'),
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $supplier),
                'pageHead'    => [
                    'title' => $supplier->name,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,

                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => SupplierTabsEnum::navigation()
                ],
                SupplierTabsEnum::SUPPLIER_PRODUCTS->value => $this->tab == SupplierTabsEnum::SUPPLIER_PRODUCTS->value ?
                    fn () => SupplierProductResource::collection(IndexSupplierProducts::run($this->supplier))
                    : Inertia::lazy(fn () => SupplierProductResource::collection(IndexSupplierProducts::run($this->supplier))),

            ]
        )->table(IndexSupplierProducts::make()->tableStructure($supplier));
    }


    #[Pure] public function jsonResponse(Supplier $supplier): SupplierResource
    {
        return new SupplierResource($supplier);
    }
}
