<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 11:45:56 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Dropshipping;

use App\Actions\Catalogue\Product\UI\GetProductShowcase;
use App\Actions\Comms\Mailshot\UI\IndexMailshots;
use App\Actions\CRM\Customer\UI\IndexCustomers;
use App\Actions\Ordering\Order\UI\IndexOrders;
use App\Actions\Retina\Dropshipping\Product\UI\IndexRetinaDropshippingPortfolio;
use App\Actions\RetinaAction;
use App\Enums\UI\Catalogue\ProductTabsEnum;
use App\Http\Resources\Catalogue\ProductsResource;
use App\Models\Catalogue\Product;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowRetinaProduct extends RetinaAction
{
    public function handle(Product $product): Product
    {
        return $product;
    }

    public function asController(Product $product, ActionRequest $request): Product
    {
        $this->initialisation($request)->withTab(ProductTabsEnum::values());

        return $this->handle($product);
    }

    public function htmlResponse(Product $product, ActionRequest $request): Response
    {
        return Inertia::render(
            'Dropshipping/Product/Product',
            [
                'title' => __('product'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
//                'navigation' => [
//                    'previous' => $this->getPrevious($product, $request),
//                    'next' => $this->getNext($product, $request),
//                ],
                'pageHead' => [
                    'title' => $product->code,
                    'model' => __('product'),
                    'icon' =>
                        [
                            'icon' => ['fal', 'fa-cube'],
                            'title' => __('product')
                        ],
                    'actions' => [
                        $this->canEdit ? [
                            'type' => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name' => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false,
                        // $this->canDelete ? [
                        //     'type' => 'button',
                        //     'style' => 'delete',
                        //     'route' => [
                        //         'name' => 'shops.show.products.remove',
                        //         'parameters' => $request->route()->originalParameters()
                        //     ]
                        // ] : false
                    ]
                ],
                'tabs' => [
                    'current' => $this->tab,
                    'navigation' => ProductTabsEnum::navigation()
                ],


                ProductTabsEnum::SHOWCASE->value => $this->tab == ProductTabsEnum::SHOWCASE->value ?
                    fn () => GetProductShowcase::run($product)
                    : Inertia::lazy(fn () => GetProductShowcase::run($product)),

                // ProductTabsEnum::ORDERS->value => $this->tab == ProductTabsEnum::ORDERS->value ?
                //     fn () => OrderResource::collection(IndexOrders::run($product->asset))
                //     : Inertia::lazy(fn () => OrderResource::collection(IndexOrders::run($product->asset))),

                // ProductTabsEnum::CUSTOMERS->value => $this->tab == ProductTabsEnum::CUSTOMERS->value ?
                //     fn () => CustomersResource::collection(IndexCustomers::run($product))
                //     : Inertia::lazy(fn () => CustomersResource::collection(IndexCustomers::run($product))),

                // ProductTabsEnum::MAILSHOTS->value => $this->tab == ProductTabsEnum::MAILSHOTS->value ?
                //     fn () => MailshotResource::collection(IndexMailshots::run($product))
                //     : Inertia::lazy(fn () => MailshotResource::collection(IndexMailshots::run($product))),


            ]
        )->table(IndexOrders::make()->tableStructure($product->asset))
            ->table(IndexCustomers::make()->tableStructure($product->shop))
            ->table(IndexMailshots::make()->tableStructure($product));
    }

    public function jsonResponse(Product $product): ProductsResource
    {
        return new ProductsResource($product);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = ''): array
    {
        $headCrumb = function (Product $product, array $routeParameters, string $suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __($product->slug),
                    ],
                ],
            ];
        };

        $portfolio = Product::where('slug', $routeParameters['product'])->first();

        return array_merge(
            IndexRetinaDropshippingPortfolio::make()->getBreadcrumbs(),
            $headCrumb(
                $portfolio,
                [
                    'index' => [
                        'name' => 'retina.dropshipping.portfolios.index',
                        'parameters' => []
                    ],
                    'model' => [
                        'name' => 'retina.dropshipping.portfolios.show',
                        'parameters' => [$portfolio->slug]
                    ]
                ],
                $suffix
            ),
        );
    }

    /*    public function getPrevious(Product $product, ActionRequest $request): ?array
        {
            $previous = Product::where('slug', '<', $product->slug)->orderBy('slug', 'desc')->first();

            return $this->getNavigation($previous, $request->route()->getName());
        }

        public function getNext(Product $product, ActionRequest $request): ?array
        {
            $next = Product::where('slug', '>', $product->slug)->orderBy('slug')->first();

            return $this->getNavigation($next, $request->route()->getName());
        }

        private function getNavigation(?Product $product, string $routeName): ?array
        {
            if (!$product) {
                return null;
            }

            return match ($routeName) {
                'shops.products.show' => [
                    'label' => $product->name,
                    'route' => [
                        'name' => $routeName,
                        'parameters' => [
                            'product' => $product->slug,
                        ],
                    ],
                ],
                'grp.org.shops.show.catalogue.products.show' => [
                    'label' => $product->name,
                    'route' => [
                        'name' => $routeName,
                        'parameters' => [
                            'organisation' => $this->parent->slug,
                            'shop' => $product->shop->slug,
                            'product' => $product->slug,
                        ],
                    ],
                ],
                'grp.org.fulfilments.show.products.show' => [
                    'label' => $product->name,
                    'route' => [
                        'name' => $routeName,
                        'parameters' => [
                            'organisation' => $this->parent->slug,
                            'fulfilment' => $product->shop->fulfilment->slug,
                            'product' => $product->slug,
                        ],
                    ],
                ],
                default => null,
            };
        }*/
}
