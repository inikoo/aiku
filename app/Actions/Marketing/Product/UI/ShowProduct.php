<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:05:53 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Product\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Marketing\ProductResource;
use App\Models\Marketing\Product;
use App\Models\Marketing\Shop;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

class ShowProduct extends InertiaAction
{
    use HasUIProduct;
    public function handle(Product $product): Product
    {
        return $product;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->can_edit = $request->user()->can('shops.products.edit');

        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function asController(Product $product, ActionRequest $request): Product
    {
        $this->initialisation($request);

        return $this->handle($product);
    }

    public function inShop(Shop $shop, Product $product, ActionRequest $request): Product
    {
        $this->initialisation($request);

        return $this->handle($product);
    }

    public function htmlResponse(Product $product): Response
    {
        return Inertia::render(
            'Marketing/Product',
            [
                'title'       => __('product'),
                'breadcrumbs' => $this->getBreadcrumbs($product),
                'pageHead'    => [
                    'title' => $product->code,
                    'edit'  => $this->can_edit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,


                ],
                'product'  => new ProductResource($product),
            ]
        );
    }

    #[Pure] public function jsonResponse(Product $product): ProductResource
    {
        return new ProductResource($product);
    }
}
