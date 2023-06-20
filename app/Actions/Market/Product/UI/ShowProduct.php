<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:05:53 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Market\Product\UI;

use App\Actions\InertiaAction;
use App\Actions\Mail\Mailshot\IndexMailshots;
use App\Actions\UI\Catalogue\CatalogueHub;
use App\Enums\UI\ProductTabsEnum;
use App\Http\Resources\Mail\MailshotResource;
use App\Http\Resources\Market\ProductResource;
use App\Http\Resources\Sales\CustomerResource;
use App\Http\Resources\Sales\OrderResource;
use App\Models\Market\Product;
use App\Models\Market\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowProduct extends InertiaAction
{
    public function handle(Product $product): Product
    {
        return $product;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('shops.products.edit');

        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function inTenant(Product $product, ActionRequest $request): Product
    {
        $this->initialisation($request)->withTab(ProductTabsEnum::values());

        return $this->handle($product);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, Product $product, ActionRequest $request): Product
    {
        $this->initialisation($request)->withTab(ProductTabsEnum::values());

        return $this->handle($product);
    }

    public function htmlResponse(Product $product, ActionRequest $request): Response
    {
        return Inertia::render(
            'Market/Product',
            [
                'title'       => __('product'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($product, $request),
                    'next'     => $this->getNext($product, $request),
                ],
                'pageHead'    => [
                    'title' => $product->code,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,


                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => ProductTabsEnum::navigation()
                ],


                ProductTabsEnum::SHOWCASE->value => $this->tab == ProductTabsEnum::SHOWCASE->value ?
                    fn () => GetProductShowcase::run($product)
                    : Inertia::lazy(fn () => GetProductShowcase::run($product)),

                ProductTabsEnum::ORDERS->value => $this->tab == ProductTabsEnum::ORDERS->value ?
                    fn () => OrderResource::collection(\App\Actions\OMS\Order\UI\IndexOrders::run($product))
                    : Inertia::lazy(fn () => OrderResource::collection(\App\Actions\OMS\Order\UI\IndexOrders::run($product))),

                ProductTabsEnum::CUSTOMERS->value => $this->tab == ProductTabsEnum::CUSTOMERS->value ?
                    fn () => CustomerResource::collection(\App\Actions\CRM\Customer\UI\IndexCustomers::run($product))
                    : Inertia::lazy(fn () => CustomerResource::collection(\App\Actions\CRM\Customer\UI\IndexCustomers::run($product))),

                ProductTabsEnum::MAILSHOTS->value => $this->tab == ProductTabsEnum::MAILSHOTS->value ?
                    fn () => MailshotResource::collection(IndexMailshots::run($product))
                    : Inertia::lazy(fn () => MailshotResource::collection(IndexMailshots::run($product))),

                /*
                ProductTabsEnum::IMAGES->value => $this->tab == ProductTabsEnum::IMAGES->value ?
                    fn () => ImagesResource::collection(IndexImages::run($product))
                    : Inertia::lazy(fn () => ImagesResource::collection(IndexImages::run($product))),
                */

            ]
        )->table(\App\Actions\OMS\Order\UI\IndexOrders::make()->tableStructure($product))
            ->table(\App\Actions\CRM\Customer\UI\IndexCustomers::make()->tableStructure($product))
            ->table(IndexMailshots::make()->tableStructure($product));
    }

    public function jsonResponse(Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (Product $product, array $routeParameters, $suffix) {
            return [

                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('products')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $product->slug,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],

            ];
        };


        return match ($routeName) {
            'shops.products.show' =>
            array_merge(
                CatalogueHub::make()->getBreadcrumbs('shops', []),
                $headCrumb(
                    $routeParameters['product'],
                    [
                        'index' => [
                            'name'       => 'shops.products.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'shops.products.show',
                            'parameters' => [
                                $routeParameters['product']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            'shops.show.products.show' =>
            array_merge(
                CatalogueHub::make()->getBreadcrumbs('shops.show.hub', ['shop' => $routeParameters['shop']]),
                $headCrumb(
                    $routeParameters['product'],
                    [
                        'index' => [
                            'name'       => 'shops.show.products.index',
                            'parameters' => [$routeParameters['shop']->slug]
                        ],
                        'model' => [
                            'name'       => 'shops.show.products.show',
                            'parameters' => [
                                $routeParameters['shop']->slug,
                                $routeParameters['product']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }

    public function getPrevious(Product $product, ActionRequest $request): ?array
    {
        $previous = Product::where('code', '<', $product->code)->orderBy('code', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Product $product, ActionRequest $request): ?array
    {
        $next = Product::where('code', '>', $product->code)->orderBy('code')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Product $product, string $routeName): ?array
    {
        if(!$product) {
            return null;
        }
        return match ($routeName) {
            'shops.products.show'=> [
                'label'=> $product->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'product'=> $product->slug
                    ]

                ]
            ],
            'shops.show.products.show'=> [
                'label'=> $product->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'shop'   => $product->shop->slug,
                        'product'=> $product->slug
                    ]

                ]
            ],
        };
    }
}
