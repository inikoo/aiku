<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Nov 2021 16:23:34 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Sales\Order;

use App\Actions\Helpers\Address\StoreImmutableAddress;
use App\Actions\WithActionUpdate;
use App\Models\Helpers\Address;
use App\Models\Sales\Order;
use Illuminate\Support\Arr;

class UpdateOrder
{
    use WithActionUpdate;

    public function handle(
        Order $order,
        array $modelData,
        Address $billingAddress,
        Address $deliveryAddress
    ): Order {
        $billingAddress  = StoreImmutableAddress::run($billingAddress);
        $deliveryAddress = StoreImmutableAddress::run($deliveryAddress);

        $modelData['delivery_address_id'] = $deliveryAddress->id;
        $modelData['billing_address_id']  = $billingAddress->id;

        $order->update(Arr::except($modelData, ['data']));
        $order->update($this->extractJson($modelData));

        return $this->update($order, $modelData, ['data']);
    }
}
