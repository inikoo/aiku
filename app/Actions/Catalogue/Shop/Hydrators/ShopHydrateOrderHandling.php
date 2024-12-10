<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 10-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Models\Catalogue\Shop;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateOrderHandling
{
    use AsAction;
    use WithEnumStats;

    private Shop $shop;

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
            'number_orders_state_created'         => $shop->orders()->where('state', OrderStateEnum::CREATING)->count(),
            'orders_state_created_amount'         => $shop->orders()->where('state', OrderStateEnum::CREATING)->sum('net_amount'),
            'orders_state_created_amount_org_currency' => $shop->orders()->where('state', OrderStateEnum::CREATING)->sum('org_net_amount'),
            'orders_state_created_amount_grp_currency' => $shop->orders()->where('state', OrderStateEnum::CREATING)->sum('grp_net_amount'),

            'number_orders_state_submitted'      => $shop->orders()->where('state', OrderStateEnum::SUBMITTED)->count(),
            'orders_state_submitted_amount'      => $shop->orders()->where('state', OrderStateEnum::SUBMITTED)->sum('net_amount'),
            'orders_state_submitted_amount_org_currency' => $shop->orders()->where('state', OrderStateEnum::SUBMITTED)->sum('org_net_amount'),
            'orders_state_submitted_amount_grp_currency' => $shop->orders()->where('state', OrderStateEnum::SUBMITTED)->sum('grp_net_amount'),

            'number_orders_state_submitted_paid' => $shop->orders()->where('state', OrderStateEnum::SUBMITTED)->where('payment_amount', '>', 0)->count(),
        ];


        // $stats = array_merge(
        //     $stats,
        //     $this->getEnumStats(
        //         model: 'offers',
        //         field: 'state',
        //         enum: OfferStateEnum::class,
        //         models: Offer::class,
        //         where: function ($q) use ($shop) {
        //             $q->where('shop_id', $shop->id);
        //         }
        //     )
        // );


        $shop->orderHandlingStats()->update($stats);
    }

}
