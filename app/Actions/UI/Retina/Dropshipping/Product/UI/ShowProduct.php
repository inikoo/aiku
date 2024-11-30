<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 14 Oct 2024 14:05:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Retina\Dropshipping\Product\UI;

use App\Actions\Catalogue\Product\UI\GetProductShowcase;
use App\Actions\CRM\Favourite\UI\IndexProductFavourites;
use App\Actions\Ordering\Order\UI\IndexOrders;
use App\Actions\RetinaAction;
use App\Actions\UI\Retina\Dashboard\ShowDashboard;
use App\Enums\UI\Catalogue\ProductTabsEnum;
use App\Http\Resources\Catalogue\ProductFavouritesResource;
use App\Http\Resources\Sales\OrderResource;
use App\Models\Catalogue\Product;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowProduct extends RetinaAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->is_root;
    }

    public function asController(Product $product, ActionRequest $request)
    {
        $this->initialisation($request);

        return $product;
    }

    public function htmlResponse(Product $product, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Catalogue/Product',
            [
                'title'       => __('product'),
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'pageHead'    => [
                    'title'   => $product->code,
                    'model'   => __('Product'),
                    'icon'    =>
                        [
                            'icon'  => ['fal', 'fa-cube'],
                            'title' => __('product')
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

                ProductTabsEnum::FAVOURITES->value => $this->tab == ProductTabsEnum::FAVOURITES->value ?
                    fn () => ProductFavouritesResource::collection(IndexProductFavourites::run($product))
                    : Inertia::lazy(fn () => ProductFavouritesResource::collection(IndexProductFavourites::run($product))),
            ]
        )->table(IndexOrders::make()->tableStructure($product->asset, ProductTabsEnum::ORDERS->value))
            ->table(IndexProductFavourites::make()->tableStructure($product, ProductTabsEnum::FAVOURITES->value));
    }

    public function getBreadcrumbs($routeParameters): array
    {
        return
            array_merge(
                ShowDashboard::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'retina.dropshipping.platform.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Channels'),
                        ]
                    ]
                ]
            );
    }
}
