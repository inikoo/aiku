<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:11 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order\Hydrators;

use App\Models\Ordering\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class OrderHydrateUniversalSearch
{
    use AsAction;


    public function handle(Order $order): void
    {
        $order->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $order->group_id,
                'organisation_id'   => $order->organisation_id,
                'organisation_slug' => $order->organisation->slug,
                'shop_id'           => $order->shop_id,
                'shop_slug'         => $order->shop->slug,
                'customer_id'       => $order->customer_id,
                'customer_slug'     => $order->customer->slug,
                'section'           => 'oms',
                'title'             => $order->number,
                'description'       => ''
            ]
        );
    }

}
