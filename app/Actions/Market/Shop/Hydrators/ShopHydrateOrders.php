<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:58:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Shop\Hydrators;

use App\Enums\OMS\Order\OrderStateEnum;
use App\Models\Market\Shop;
use App\Models\OMS\Order;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateOrders implements ShouldBeUnique
{
    use AsAction;


    public function handle(Shop $shop): void
    {
        $stats = [
            'number_orders' => $shop->orders->count(),
        ];

        $stateCounts = Order::where('shop_id', $shop->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach (OrderStateEnum::cases() as $orderState) {
            $stats['number_orders_state_' . $orderState->snake()] = Arr::get($stateCounts, $orderState->value, 0);
        }

        $shop->crmStats()->update($stats);
    }

    public function getJobUniqueId(Shop $shop): string
    {
        return $shop->id;
    }
}
