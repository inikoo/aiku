<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 05 Dec 2021 15:37:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Delivery\DeliveryNote;

use App\Actions\Helpers\Address\StoreImmutableAddress;
use App\Models\Utils\ActionResult;
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

    ): ActionResult
    {
        $res = new ActionResult();

        $modelData['organisation_id']=$order->organisation_id;
        $modelData['shop_id']=$order->shop_id;
        $modelData['customer_id']=$order->customer_id;
        $modelData['order_id']=$order->id;

        $deliveryAddress=StoreImmutableAddress::run($deliveryAddress);
        $modelData['delivery_address_id']=$deliveryAddress->id;

        /** @var \App\Models\Delivery\DeliveryNote $deliveryNote */
        $deliveryNote = $order->deliveryNotes()->create($modelData);



        $res->model    = $deliveryNote;
        $res->model_id = $deliveryNote->id;
        $res->status   = $res->model_id ? 'inserted' : 'error';

        return $res;
    }
}
