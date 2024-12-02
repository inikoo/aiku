<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 24-10-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Dropshipping\Portfolio\Hydrators;

use App\Actions\OrgAction;
use App\Actions\Traits\WithEnumStats;
use App\Enums\Catalogue\Portfolio\PortfolioTypeEnum;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Models\Catalogue\Shop;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydratePortfolios extends OrgAction
{
    use AsAction;
    use WithEnumStats;


    protected Shop $shop;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->shop->id))->dontRelease()];
    }

    public function handle(Shop $shop): void
    {
        $stats = [
            'number_customer_clients'                  => $shop->clients()->count(),
            'number_current_customer_clients'          => $shop->clients()->where('status', true)->count(),
            'number_portfolios'                        => $shop->portfolios()->count(),
            'number_products'                          => $shop->products()->count(),
            'number_current_portfolios'                => $shop->portfolios()->where('status', true)->count(),
            'number_current_products'                  => $shop->products()->where('status', true)->count(),
            'number_portfolios_platform_shopify'          => $shop->portfolios()->where('type', PortfolioTypeEnum::SHOPIFY->value)->count(),
            'number_portfolios_platform_woocommerce'      => $shop->portfolios()->where('type', PortfolioTypeEnum::WOOCOMMERCE->value)->count(),
        ];

        foreach (ProductStateEnum::cases() as $case) {
            $stats['number_products_state_'.$case->snake()] = $shop->products()->where('state', $case->value)->count();
        }

        $shop->dropshippingStats()->updateOrCreate($stats);
    }
}
