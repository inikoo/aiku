<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 22 Feb 2023 11:05:25 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Product;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\IndexShops;
use App\Actions\UI\WithInertia;
use App\Http\Resources\Marketing\DepartmentResource;
use App\Http\Resources\Marketing\FamilyResource;
use App\Http\Resources\Marketing\ProductResource;
use App\Models\Marketing\Department;
use App\Models\Marketing\Family;
use App\Models\Marketing\Product;
use App\Models\Marketing\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;


class ShowProduct extends InertiaAction
{
    use AsAction;
    use WithInertia;

    public function handle(Product $product): Product
    {
        return $product;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function asController(Product $product): Product
    {
        return $this->handle($product);
    }

    public function inShop(Shop $shop, Product $product, Request $request): Product
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($product);
    }

    public function htmlResponse(Product $product): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Marketing/Product',
            [
                'title' => __('product'),
                'breadcrumbs' => $this->getBreadcrumbs($product),
                'pageHead' => [
                    'title' => $product->name,


                ],
                'product' => new ProductResource($product),
                'treeMaps' => [
                    [
                        [
                            'name' => __('products'),
                            'icon' => ['fal', 'fa-cube'],
                            'href' => ['shops.show.products.index', $product->slug],
                            'index' => [
                                'number' => $product->stats->number_products
                            ]
                        ],
                    ],
                ]
            ]
        );
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->can('hr.edit'));
        $this->set('canViewUsers', $request->user()->can('users.view'));
    }

    #[Pure] public function jsonResponse(Product $product): ProductResource
    {
        return new ProductResource($product);
    }


    public function getBreadcrumbs(Product $product): array
    {
        return array_merge(
            (new IndexShops())->getBreadcrumbs(),
            [
                'shops.show' => [
                    'route' => 'shops.show',
                    'routeParameters' => $product->id,
                    'name' => $product->code,
                    'index' => [
                        'route' => 'shops.index',
                        'overlay' => __('Products list')
                    ],
                    'modelLabel' => [
                        'label' => __('product')
                    ],
                ],
            ]
        );
    }

}
