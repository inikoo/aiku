<?php
/*
 * Author: Vika Aqordi <aqordivika@yahoo.co.id>
 * Created on: 19-08-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

namespace App\Actions\UI\Retina\Dropshipping;

use App\Actions\RetinaAction;
use App\Actions\Catalogue\Product\UI\IndexProducts as IndexUIProducts;
use App\Actions\UI\Retina\Dashboard\ShowDashboard;
use App\Enums\UI\Catalogue\ProductTabsEnum;
use App\Http\Resources\Catalogue\DropshippingPortfolioResource;
use App\Models\Dropshipping\ShopifyUser;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class IndexDropshippingRetinaPortfolio extends RetinaAction
{
    public function handle(ShopifyUser $shopifyUser): ShopifyUser
    {
        return $shopifyUser;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->is_root;
    }

    public function asController(ActionRequest $request): ShopifyUser
    {
        $this->initialisation($request);

        $shopifyUser = $request->user()->customer->shopifyUser;

        return $this->handle($shopifyUser);
    }

    public function htmlResponse(ShopifyUser $shopifyUser): Response
    {
        return Inertia::render(
            'Dropshipping/Portfolios',
            [
                 'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('My Portfolios'),
                'pageHead'    => [
                    'title' => __('My Portfolios'),
                    'icon'  => 'fal fa-cube'
                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => ProductTabsEnum::navigation()
                ],

                'products' => DropshippingPortfolioResource::collection(IndexUIProducts::run($shopifyUser, 'products'))
            ]
        )->table(IndexUIProducts::make()->tableStructure($shopifyUser, prefix: 'products'));
    }

    public function getBreadcrumbs(): array
    {
        return
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'retina.dropshipping.portfolios.index'
                            ],
                            'label'  => __('My Portfolio'),
                        ]
                    ]
                ]
            );
    }
}
