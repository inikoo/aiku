<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 19:36:35 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\DropshippingCustomerPortfolio\Hydrators;

use App\Actions\OrgAction;
use App\Actions\Traits\WithEnumStats;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Models\Dropshipping\DropshippingCustomerPortfolio;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class HydrateDropshippingCustomerPortfolio extends OrgAction
{
    use AsAction;
    use WithEnumStats;


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
            'amount'                  => $dropshippingCustomerPortfolio->customer->orders()->sum('net'),
            'number_orders'           => $dropshippingCustomerPortfolio->customer->orders()->count(),
            'number_ordered_quantity' => $dropshippingCustomerPortfolio->customer->orders()->where('state', OrderStateEnum::DISPATCHED->value)->count(),
            'number_clients'          => $dropshippingCustomerPortfolio->customer->clients()->count(),
            'last_ordered_at'         => $dropshippingCustomerPortfolio->last_added_at,
        ];

        $dropshippingCustomerPortfolio->stats()->update($stats);
    }
}
