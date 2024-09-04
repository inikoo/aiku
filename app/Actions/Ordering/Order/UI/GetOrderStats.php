<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order\UI;

use App\Models\Catalogue\Shop;
use App\Models\Ordering\Order;
use Lorisleiva\Actions\Concerns\AsObject;

class GetOrderStats
{
    use AsObject;

    public function handle(Shop $shop): array
    {
        $orders = $shop->orders;
        $total  = $orders->sum('net_amount');
        return [
            'number_orders' => $shop->salesStats->number_orders,
            'total'         => $total
        ];
    }
}
