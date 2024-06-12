<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 20 Apr 2024 22:38:08 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\DropshippingCustomerPortfolio\Hydrators;

use App\Models\Dropshipping\DropshippingCustomerPortfolio;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class HydrateDropshippingCustomerPortfolio
{
    use AsAction;

    private DropshippingCustomerPortfolio $dropshippingCustomerPortfolio;

    public function __construct(DropshippingCustomerPortfolio $dropshippingCustomerPortfolio)
    {
        $this->dropshippingCustomerPortfolio = $dropshippingCustomerPortfolio;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->dropshippingCustomerPortfolio->id))->dontRelease()];
    }

    public function handle(DropshippingCustomerPortfolio $dropshippingCustomerPortfolio): void
    {
        $stats = [
            'amount'                  => $dropshippingCustomerPortfolio->count(),
            'number_ordered_quantity' => $dropshippingCustomerPortfolio->dropshippingCustomerPortfolios()->count(),
            'number_clients'          => $dropshippingCustomerPortfolio->dropshippingCustomerPortfolios()->count(),
            'last_ordered_at'         => $dropshippingCustomerPortfolio->dropshippingCustomerPortfolios()->count(),
            'removed_at'              => $dropshippingCustomerPortfolio->dropshippingCustomerPortfolios()->where('status', true)->count()
        ];

        $dropshippingCustomerPortfolio->stats()->update($stats);
    }


}
