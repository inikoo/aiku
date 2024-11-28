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


    private Portfolio $portfolio;

    public function __construct(Portfolio $portfolio)
    {
        $this->portfolio = $portfolio;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->portfolio->id))->dontRelease()];
    }


    public function handle(Portfolio $portfolio): void
    {
        $customer = $portfolio->customer;

        // ALl this is wrong, it should only take stats of product_id in portfolio, it should query transactions table
        //        $stats = [
        //            'amount'                  => $customer->orders()->sum('net_amount'),
        //            'number_orders'           => $customer->orders()->count(),
        //            'number_ordered_quantity' => $customer->orders()->where('state', OrderStateEnum::DISPATCHED->value)->count(),
        //            'number_clients'          => $customer->clients()->count(),
        //            'last_ordered_at'         => $portfolio->last_added_at,
        //        ];
        //
        //        $portfolio->stats()->update($stats);
    }
}
