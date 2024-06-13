<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 20 Apr 2024 22:38:08 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Models\SysAdmin\Organisation;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateDropshipping
{
    use AsAction;

    private Organisation $organisation;

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
            'number_dropshipping_customer_portfolios'            => $organisation->dropshippingCustomerPortfolios()->count(),
            'number_current_dropshipping_customer_portfolios'    => $organisation->dropshippingCustomerPortfolios()->where('status', true)->count(),
            'number_products'                                    => $organisation->products()->count(),
            'number_current_products'                            => $organisation->products()->where('status', true)->count(),
        ];

        foreach (ProductStateEnum::cases() as $case) {
            $stats['number_products_state_'.$case->snake()] = $organisation->products()->where('state', $case->value)->count();
        }

        $organisation->dropshippingStats()->update($stats);
    }


}
