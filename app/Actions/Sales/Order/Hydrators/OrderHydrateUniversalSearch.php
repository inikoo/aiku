<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Sales\Order\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Sales\Order;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class OrderHydrateUniversalSearch implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Order $order): void
    {
        $order->universalSearch()->create(
            [
                'primary_term'   => $order->customer_id.' '.$order->number,
                'secondary_term' => $order->shop_id.' '.$order->date
            ]
        );
    }

    public function getJobUniqueId(Order $order): int
    {
        return $order->id;
    }
}
