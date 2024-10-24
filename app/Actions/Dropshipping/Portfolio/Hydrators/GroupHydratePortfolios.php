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
use App\Models\SysAdmin\Group;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydratePortfolios extends OrgAction
{
    use AsAction;
    use WithEnumStats;


    private Portfolio $dropshippingCustomerPortfolio;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->group->id))->dontRelease()];
    }

    public function handle(Group $group): void
    {
        $stats = [
            'number_customer_clients'          => $group->clients()->count(),
            'number_current_customer_clients'          => $group->clients()->where('status', true)->count(),
            'number_portfolios'          => $group->portfolios()->count(),
            'number_current_portfolios'          => $group->portfolios()->where('status', true)->count(),
            'number_products'          => $group->products()->count(),
            'number_current_products'          => $group->products()->where('status', true)->count(),
            'number_portfolios_platform_shopify'          => $group->portfolios()->where('type', PortfolioTypeEnum::SHOPIFY->value),
            'number_portfolios_platform_woocommerce'      => $group->portfolios()->where('type', PortfolioTypeEnum::WOOCOMMERCE->value),
        ];

        $group->dropshippingStats()->update($stats);
    }
}
