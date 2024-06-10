<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 11:45:56 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product\UI;

use App\Actions\Catalogue\ProductCategory\UI\ShowFamily;
use App\Actions\Catalogue\Shop\UI\IndexShops;
use App\Actions\Catalogue\Shop\UI\ShowCatalogue;
use App\Actions\CRM\Customer\UI\IndexCustomers;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Mail\Mailshot\IndexMailshots;
use App\Actions\Ordering\Order\UI\IndexOrders;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HaCatalogueAuthorisation;
use App\Enums\UI\Catalogue\ProductTabsEnum;
use App\Http\Resources\Catalogue\ProductsResource;
use App\Http\Resources\CRM\CustomersResource;
use App\Http\Resources\Mail\MailshotResource;
use App\Http\Resources\Sales\OrderResource;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowProduct extends OrgAction
{
    use HaCatalogueAuthorisation;

    private Organisation|Shop|Fulfilment|ProductCategory $parent;

    public function handle(Product $product): Product
    {
        return $product;
    }


    public function inOrganisation(Organisation $organisation, Product $product, ActionRequest $request): Product
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request)->withTab(ProductTabsEnum::values());

        return $this->handle($product);
    }

    public function asController(Organisation $organisation, Shop $shop, Product $product, ActionRequest $request): Product
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request)->withTab(ProductTabsEnum::values());

        return $this->handle($product);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inDepartment(Organisation $organisation, Shop $shop, ProductCategory $department, Product $product, ActionRequest $request): Product
    {
        $this->parent = $department;
        $this->initialisationFromShop($shop, $request)->withTab(ProductTabsEnum::values());

        return $this->handle($product);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFamily(Organisation $organisation, Shop $shop, ProductCategory $family, Product $product, ActionRequest $request): Product
    {
        $this->parent = $family;
        $this->initialisationFromShop($shop, $request)->withTab(ProductTabsEnum::values());

        return $this->handle($product);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inFamilyinDepartment(Organisation $organisation, Shop $shop, ProductCategory $department, ProductCategory $family, Product $product, ActionRequest $request): Product
    {
        $this->parent = $family;
        $this->initialisationFromShop($shop, $request)->withTab(ProductTabsEnum::values());

        return $this->handle($product);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Product $product, ActionRequest $request): Product
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(ProductTabsEnum::values());

        return $this->handle($product);
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
                'navigation'  => [
                    'previous' => $this->getPrevious($product, $request),
                    'next'     => $this->getNext($product, $request),
                ],
                'pageHead'    => [
                    'title'   => $product->code,
                    'model'   => __('product'),
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
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => ProductTabsEnum::navigation()
                ],


                ProductTabsEnum::SHOWCASE->value => $this->tab == ProductTabsEnum::SHOWCASE->value ?
                    fn () => GetProductShowcase::run($product)
                    : Inertia::lazy(fn () => GetProductShowcase::run($product)),

                ProductTabsEnum::ORDERS->value => $this->tab == ProductTabsEnum::ORDERS->value ?
                    fn () => OrderResource::collection(IndexOrders::run($product->asset))
                    : Inertia::lazy(fn () => OrderResource::collection(IndexOrders::run($product->asset))),

                ProductTabsEnum::CUSTOMERS->value => $this->tab == ProductTabsEnum::CUSTOMERS->value ?
                    fn () => CustomersResource::collection(IndexCustomers::run($product))
                    : Inertia::lazy(fn () => CustomersResource::collection(IndexCustomers::run($product))),

                ProductTabsEnum::MAILSHOTS->value => $this->tab == ProductTabsEnum::MAILSHOTS->value ?
                    fn () => MailshotResource::collection(IndexMailshots::run($product))
                    : Inertia::lazy(fn () => MailshotResource::collection(IndexMailshots::run($product))),


            ]
        )->table(IndexOrders::make()->tableStructure($product->asset))
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
                            'label' => __('Products')
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

        $product = Product::where('slug', $routeParameters['product'])->first();

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
                    $product,
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
                    $product,
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
            'grp.org.shops.show.catalogue.families.show.products.show' =>
            array_merge(
                ShowFamily::make()->getBreadcrumbs('grp.org.shops.show.catalogue.families.show', Arr::only($routeParameters, ['organisation', 'shop', 'family'])),
                $headCrumb(
                    $product,
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.catalogue.families.show.products.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'shop', 'family'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.catalogue.families.show.products.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.shops.show.catalogue.departments.show.families.show.products.show' =>
            array_merge(
                ShowFamily::make()->getBreadcrumbs('grp.org.shops.show.catalogue.departments.show.families.show', Arr::only($routeParameters, ['organisation', 'shop', 'department', 'family'])),
                $headCrumb(
                    $product,
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.catalogue.departments.show.families.show.products.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'shop', 'department', 'family'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.catalogue.departments.show.families.show.products.show',
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
