<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 14 Oct 2024 14:05:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Dropshipping\Product\UI;

use App\Actions\Catalogue\Product\UI\IndexProducts as IndexUIProducts;
use App\Actions\Retina\UI\Dashboard\ShowRetinaDashboard;
use App\Actions\RetinaAction;
use App\Enums\Ordering\Platform\PlatformTypeEnum;
use App\Enums\UI\Catalogue\ProductTabsEnum;
use App\Http\Resources\Catalogue\ProductsResource;
use App\Models\CRM\Customer;
use App\Models\Dropshipping\Platform;
use App\Models\Dropshipping\ShopifyUser;
use App\Models\Dropshipping\TiktokUser;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class IndexRetinaDropshippingProducts extends RetinaAction
{
    public function handle(ShopifyUser|Customer|TiktokUser $scope): ShopifyUser|Customer|TiktokUser
    {
        return $scope;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->is_root;
    }

    public function asController(ActionRequest $request): Customer
    {
        $this->initialisation($request);

        $customer = $request->user()->customer;

        return $this->handle($customer);
    }

    public function inPlatform(Platform $platform, ActionRequest $request): ShopifyUser|TiktokUser
    {
        $this->initialisation($request);

        if ($platform->type === PlatformTypeEnum::SHOPIFY) {
            $scope = $request->user()->customer->shopifyUser;
        } else {
            $scope = $request->user()->customer->tiktokUser;
        }

        return $this->handle($scope);
    }

    public function htmlResponse(ShopifyUser|Customer|TiktokUser $scope): Response
    {
        if ($scope instanceof ShopifyUser) {
            $shop = $scope->customer->shop;
            $routes = [
                'store_product' => [
                    'name'       => 'retina.models.dropshipping.shopify_user.product.store',
                    'parameters' => [
                        'shopifyUser' => $scope->id
                    ]
                ],
            ];
        } elseif ($scope instanceof TiktokUser) {
            $shop = $scope->customer->shop;
            $routes = [
                'store_product' => [
                    'name'       => 'retina.models.dropshipping.tiktok.product.store',
                    'parameters' => [
                        'tiktokUser' => $scope->id
                    ]
                ],
            ];
        } else {
            $shop = $scope->shop;
            $routes = [
                'store_product' => [
                    'name'       => 'retina.models.dropshipping.customer.product.store',
                    'parameters' => [
                        'customer' => $scope->id
                    ]
                ],
            ];
        }

        return Inertia::render(
            'Dropshipping/Products',
            [
                 'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('All Products'),
                'pageHead'    => [
                    'title' => __('All Products'),
                    'icon'  => 'fal fa-cube'
                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => ProductTabsEnum::navigation()
                ],
                'routes' => $routes,

                'products' => ProductsResource::collection(IndexUIProducts::make()->inDropshipping($scope, 'all'))
            ]
        )->table(IndexUIProducts::make()->tableStructure($shop, prefix: 'products'));
    }

    public function getBreadcrumbs(): array
    {
        return
            array_merge(
                ShowRetinaDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'retina.dropshipping.portfolios.products.index'
                            ],
                            'label'  => __('Products'),
                        ]
                    ]
                ]
            );
    }
}
