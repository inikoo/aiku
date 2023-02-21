<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Nov 2021 16:23:34 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Sales\Order;

use App\Actions\WithActionUpdate;
use App\Models\Sales\Order;
use Illuminate\Support\Arr;

class UpdateOrder
{
    use WithActionUpdate;

    public function handle(
        Order $order,
        array $modelData
    ): Order {



        return $this->update($order, $modelData, ['data']);
    }
}
