<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Nov 2021 16:15:50 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Sales\Order;

use App\Actions\Helpers\Address\StoreImmutableAddress;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Helpers\Address;
use App\Models\Sales\Customer;
use App\Models\Sales\Order;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreOrder
{
    use AsAction;

    public function handle(
        Customer|CustomerClient $parent,
        array $modelData,
        Address $billingAddress,
        Address $deliveryAddress

    ): Order {
        if (class_basename($parent) == 'Customer') {
            $modelData['customer_id'] = $parent->id;
        } else {
            $modelData['customer_id']        = $parent->customer_id;
            $modelData['customer_client_id'] = $parent->id;
        }

        $modelData['currency_id'] = $parent->shop->currency_id;
        $modelData['shop_id']     = $parent->shop_id;

        if (!Arr::exists($modelData, 'type')) {
            $modelData['type'] = $parent->shop->subtype;
        }

        $billingAddress  = StoreImmutableAddress::run($billingAddress);
        $deliveryAddress = StoreImmutableAddress::run($deliveryAddress);


        $modelData['delivery_address_id'] = $deliveryAddress->id;
        $modelData['billing_address_id']  = $billingAddress->id;

        /** @var Order $order */
        $order = $parent->shop->orders()->create($modelData);
        $order->stats()->create();
        HydrateOrder::make()->originalItems($order);

        return $order;
    }
}
