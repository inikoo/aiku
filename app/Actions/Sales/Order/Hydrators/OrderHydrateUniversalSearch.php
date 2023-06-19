<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Sales\Order\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Sales\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class OrderHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Order $order): void
    {
        $order->universalSearch()->create(
            [
                'section' => 'CRM',
                'route'   => json_encode([
                    'name'      => 'customers.show.orders.show',
                    'arguments' => [
                        $order->customer->slug,
                        $order->slug
                    ]
                ]),
                'icon'           => 'fa-money-check-alt',
                'primary_term'   => $order->customer_id.' '.$order->number,
                'secondary_term' => $order->shop_id.' '.$order->date
            ]
        );
    }

}
