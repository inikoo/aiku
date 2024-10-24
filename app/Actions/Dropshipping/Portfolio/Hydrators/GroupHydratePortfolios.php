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
use App\Models\Dropshipping\Portfolio;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydratePortfolios extends OrgAction
{
    use AsAction;
    use WithEnumStats;


    private Portfolio $dropshippingCustomerPortfolio;

    public function __construct(Portfolio $dropshippingCustomerPortfolio)
    {
        $this->dropshippingCustomerPortfolio = $dropshippingCustomerPortfolio;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->dropshippingCustomerPortfolio->id))->dontRelease()];
    }


    public function handle(Portfolio $dropshippingCustomerPortfolio): void
    {
        $group = $dropshippingCustomerPortfolio->group;
        // dd($group->customers()->count());
        $stats = [
            'number_customer_clients'          => $group->customers()->count(),
            'number_current_customer_clients'          => $dropshippingCustomerPortfolio->customer->clients()->count(),
            'number_portfolios'          => $group->portfolios()->count(),
            'number_current_portfolios'          => $dropshippingCustomerPortfolio->count(),
            'number_products'          => $group->products()->count(),
            'number_current_products'          => $dropshippingCustomerPortfolio->product()->count(),
            'number_portfolios_platform_shopify'          => $group->portfolios()->where('type', PortfolioTypeEnum::SHOPIFY->value),
            'number_portfolios_platform_shopify'          => $group->portfolios()->where('type', PortfolioTypeEnum::SHOPIFY->value),
        ];

        $dropshippingCustomerPortfolio->group->dropshippingStats()->update($stats);
    }
}
