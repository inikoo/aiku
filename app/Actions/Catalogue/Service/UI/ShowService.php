<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:05:53 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\Service\UI;

use App\Actions\Catalogue\Product\UI\GetProductRental;
use App\Actions\Catalogue\Product\UI\GetProductService;
use App\Actions\Catalogue\Product\UI\GetProductShowcase;
use App\Actions\Catalogue\Shop\UI\IndexShops;
use App\Actions\Catalogue\Shop\UI\ShowCatalogue;
use App\Actions\CRM\Customer\UI\IndexCustomers;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Mail\Mailshot\IndexMailshots;
use App\Actions\Ordering\Order\UI\IndexOrders;
use App\Actions\OrgAction;
use App\Enums\UI\Catalogue\ProductTabsEnum;
use App\Enums\UI\Catalogue\ServiceTabsEnum;
use App\Http\Resources\Catalogue\ProductsResource;
use App\Http\Resources\CRM\CustomersResource;
use App\Http\Resources\Mail\MailshotResource;
use App\Http\Resources\Sales\OrderResource;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Service;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowService extends OrgAction
{
    private Organisation|Shop|Fulfilment|ProductCategory $parent;

    public function handle(Product $product): Product
    {
        return $product;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->parent instanceof Fulfilment) {
            $this->canEdit   = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
            $this->canDelete = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
        } elseif($this->parent instanceof Organisation) {
            $this->canEdit   = $request->user()->hasPermissionTo("shops.{$this->organisation->id}.edit");
            $this->canDelete = $request->user()->hasPermissionTo("shops.{$this->organisation->id}.edit");
            return $request->user()->hasPermissionTo("shops.{$this->organisation->id}.view");
        } else {
            $this->canEdit   = $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");
            $this->canDelete = $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");
            return $request->user()->hasPermissionTo("products.{$this->shop->id}.view");
        }


    }

    public function inOrganisation(Organisation $organisation, Product $product, ActionRequest $request): Product
    {
        $this->parent= $organisation;
        $this->initialisation($organisation, $request)->withTab(ProductTabsEnum::values());
        return $this->handle($product);
    }

    public function asController(Organisation $organisation, Shop $shop, Product $product, ActionRequest $request): Product
    {
        $this->parent= $shop;
        $this->initialisationFromShop($shop, $request)->withTab(ProductTabsEnum::values());
        return $this->handle($product);
    }

    public function inDepartment(Organisation $organisation, Shop $shop, ProductCategory $department, Product $product, ActionRequest $request): Product
    {
        $this->parent= $department;
        $this->initialisationFromShop($shop, $request)->withTab(ProductTabsEnum::values());

        return $this->handle($product);
    }

    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Service $service, ActionRequest $request): Product
    {
        $this->parent= $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(ProductTabsEnum::values());
        return $this->handle($service->product);
    }

    public function htmlResponse(Product $product, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Catalogue/Product',
            [
                'title'       => __('product'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($product, $request),
                    'next'     => $this->getNext($product, $request),
                ],
                'pageHead'    => [
                    'title'   => $product->code,
                    'icon'    =>
                        [
                            'icon'  => ['fal', 'fa-cube'],
                            'title' => __('product')
                        ],
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false,
                        $this->canDelete ? [
                            'type'  => 'button',
                            'style' => 'delete',
                            'route' => [
                                'name'       => 'shops.show.products.remove',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false
                    ]
                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => ServiceTabsEnum::navigation()
                ],


                ServiceTabsEnum::SHOWCASE->value => $this->tab == ServiceTabsEnum::SHOWCASE->value ?
                    fn () => GetProductShowcase::run($product)
                    : Inertia::lazy(fn () => GetProductShowcase::run($product)),

                ServiceTabsEnum::ORDERS->value => $this->tab == ServiceTabsEnum::ORDERS->value ?
                    fn () => OrderResource::collection(IndexOrders::run($product))
                    : Inertia::lazy(fn () => OrderResource::collection(IndexOrders::run($product))),

                ServiceTabsEnum::CUSTOMERS->value => $this->tab == ServiceTabsEnum::CUSTOMERS->value ?
                    fn () => CustomersResource::collection(IndexCustomers::run($product))
                    : Inertia::lazy(fn () => CustomersResource::collection(IndexCustomers::run($product))),

                ServiceTabsEnum::MAILSHOTS->value => $this->tab == ServiceTabsEnum::MAILSHOTS->value ?
                    fn () => MailshotResource::collection(IndexMailshots::run($product))
                    : Inertia::lazy(fn () => MailshotResource::collection(IndexMailshots::run($product))),

                // ProductTabsEnum::SERVICE->value => $this->tab == ProductTabsEnum::SERVICE->value ?
                //     fn () => GetProductService::run($product)
                //     : Inertia::lazy(fn () => GetProductService::run($product)),

                // ProductTabsEnum::RENTAL->value => $this->tab == ProductTabsEnum::RENTAL->value ?
                //     fn () => GetProductRental::run($product)
                //     : Inertia::lazy(fn () => GetProductRental::run($product)),

                // ProductTabsEnum::SERVICE->value => $this->tab == ProductTabsEnum::SERVICE->value ?
                //     fn () => MailshotResource::collection(IndexMailshots::run($product))
                //     : Inertia::lazy(fn () => MailshotResource::collection(IndexMailshots::run($product))),

                /*
                ProductTabsEnum::IMAGES->value => $this->tab == ProductTabsEnum::IMAGES->value ?
                    fn () => ImagesResource::collection(IndexImages::run($product))
                    : Inertia::lazy(fn () => ImagesResource::collection(IndexImages::run($product))),
                */

            ]
        )->table(IndexOrders::make()->tableStructure($product))
            ->table(IndexCustomers::make()->tableStructure($product))
            ->table(IndexMailshots::make()->tableStructure($product));
    }

    public function jsonResponse(Product $product): ProductsResource
    {
        return new ProductsResource($product);
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

        $service=Service::where('id', $routeParameters['service'])->first();

        return match ($routeName) {
            'shops.products.show' =>
            array_merge(
                IndexShops::make()->getBreadcrumbs('grp.org.shops.index', $routeParameters['organisation']),
                $headCrumb(
                    $routeParameters['product'],
                    [
                        'index' => [
                            'name'       => 'shops.products.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'shops.products.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.shops.show.catalogue.products.show' =>
            array_merge(
                ShowCatalogue::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $service,
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.catalogue.products.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.catalogue.products.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.fulfilments.show.products.show' =>
            array_merge(
                ShowFulfilment::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $service,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.products.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.products.show',
                            'parameters' => $routeParameters
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
                    'name'       => $routeName,
                    'parameters' => [
                        'product' => $product->slug,
                    ],
                ],
            ],
            'grp.org.shops.show.catalogue.products.show' => [
                'label' => $product->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $this->parent->slug,
                        'shop'         => $product->shop->slug,
                        'product'      => $product->slug,
                    ],
                ],
            ],
            'grp.org.fulfilments.show.products.show' => [
                'label' => $product->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $this->parent->slug,
                        'fulfilment'   => $product->shop->fulfilment->slug,
                        'product'      => $product->slug,
                    ],
                ],
            ],
            default => null,
        };
    }
}
