<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:09:31 Central European Standard Time, Malaga, Spain
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

class EditProduct extends InertiaAction
{
    use HasUIProduct;
    public function handle(Product $product): Product
    {
        return $product;
    }

    public function authorize(ActionRequest $request): bool
    {

        return $request->user()->hasPermissionTo("shops.products.edit");
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
                    'exitEdit'  => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ],


                ],
                'product'  => new ProductResource($product),
                'treeMaps' => [
                    [
                        [
                            'name'  => __('products'),
                            'icon'  => ['fal', 'fa-cube'],
                            'href'  => ['shops.show.products.index', $product->slug],
                            'index' => [
                                'number' => $product->stats->number_products
                            ]
                        ],
                    ],
                ]
            ]
        );
    }

    #[Pure] public function jsonResponse(Product $product): ProductResource
    {
        return new ProductResource($product);
    }

}
