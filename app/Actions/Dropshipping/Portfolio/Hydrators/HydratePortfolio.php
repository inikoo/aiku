<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 19:36:35 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Portfolio\Hydrators;

use App\Actions\OrgAction;
use App\Actions\Traits\WithEnumStats;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Models\Dropshipping\Portfolio;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class HydratePortfolio extends OrgAction
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
        $stats = [
            'amount'                  => $dropshippingCustomerPortfolio->customer->orders()->sum('net'),
            'number_orders'           => $dropshippingCustomerPortfolio->customer->orders()->count(),
            'number_ordered_quantity' => $dropshippingCustomerPortfolio->customer->orders()->where('state', OrderStateEnum::DISPATCHED->value)->count(),
            'number_clients'          => $dropshippingCustomerPortfolio->customer->clients()->count(),
            'last_ordered_at'         => $dropshippingCustomerPortfolio->last_added_at,
        ];

        $dropshippingCustomerPortfolio->stats()->update($stats);
    }
}
