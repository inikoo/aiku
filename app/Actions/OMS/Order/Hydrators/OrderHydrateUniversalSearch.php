<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:11 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\Order\Hydrators;

use App\Actions\Traits\WithTenantJob;
use App\Models\OMS\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class OrderHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Order $order): void
    {
        $order->universalSearch()->updateOrCreate(
            [],
            [
                'section' => 'oms',
                'route'   => json_encode([
                    'name'      => 'customers.show.orders.show',
                    'arguments' => [
                        $order->customer->slug,
                        $order->slug
                    ]
                ]),
                'icon'           => 'fa-money-check-alt',
                'title'          => $order->customer_id.' '.$order->number,
                'description'    => $order->shop_id.' '.$order->date
            ]
        );
    }

}
