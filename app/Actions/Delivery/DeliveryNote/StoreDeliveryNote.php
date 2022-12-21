<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 05 Dec 2021 15:37:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Delivery\DeliveryNote;

use App\Actions\Helpers\Address\StoreImmutableAddress;
use App\Models\Delivery\DeliveryNote;
use App\Models\Helpers\Address;
use App\Models\Sales\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreDeliveryNote
{
    use AsAction;

    public function handle(
        Order $order,
        Address $deliveryAddress,
        array $modelData

    ): DeliveryNote {
        $modelData['shop_id']     = $order->shop_id;
        $modelData['customer_id'] = $order->customer_id;
        $modelData['order_id']    = $order->id;

        $deliveryAddress                  = StoreImmutableAddress::run($deliveryAddress);
        $modelData['delivery_address_id'] = $deliveryAddress->id;

        /** @var DeliveryNote $deliveryNote */
        $deliveryNote = $order->deliveryNotes()->create($modelData);
        $deliveryNote->stats()->create();

        return $deliveryNote;
    }
}
