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
use App\Models\SysAdmin\Organisation;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydratePortfolios extends OrgAction
{
    use AsAction;
    use WithEnumStats;


    protected Organisation $organisation;

    public function __construct(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->organisation->id))->dontRelease()];
    }

    public function handle(Organisation $organisation): void
    {
        $stats = [
            'number_customer_clients'                  => $organisation->clients()->count(),
            'number_current_customer_clients'          => $organisation->clients()->where('status', true)->count(),
            'number_portfolios'                        => $organisation->portfolios()->count(),
            'number_products'                          => $organisation->products()->count(),
            'number_current_portfolios'                => $organisation->portfolios()->where('status', true)->count(),
            'number_current_products'                  => $organisation->products()->where('status', true)->count(),
            'number_portfolios_platform_shopify'          => $organisation->portfolios()->where('type', PortfolioTypeEnum::SHOPIFY->value)->count(),
            'number_portfolios_platform_woocommerce'      => $organisation->portfolios()->where('type', PortfolioTypeEnum::WOOCOMMERCE->value)->count(),
        ];

        foreach (ProductStateEnum::cases() as $case) {
            $stats['number_products_state_'.$case->snake()] = $organisation->products()->where('state', $case->value)->count();
        }

        $organisation->dropshippingStats()->updateOrCreate($stats);
    }
}
