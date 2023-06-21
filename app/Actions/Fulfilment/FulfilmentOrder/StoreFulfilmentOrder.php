<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 27 Nov 2022 13:15:49 Central Indonesia Time, Ubud, Bali, Indonesia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentOrder;

use App\Actions\Fulfilment\FulfilmentOrderItem\StoreFulfilmentOrderItem;
use App\Actions\Helpers\Address\AttachHistoricAddressToModel;
use App\Actions\Helpers\Address\StoreHistoricAddress;
use App\Models\CRM\Customer;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Fulfilment\FulfilmentOrder;
use App\Models\Helpers\Address;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreFulfilmentOrder
{
    use AsAction;

    public function handle(
        Customer|CustomerClient $parent,
        array $modelData,
        Address $deliveryAddress,
        array $items
    ): FulfilmentOrder {
        if (class_basename($parent) == 'Customer') {
            $modelData['customer_id'] = $parent->id;
        } else {
            $modelData['customer_id']        = $parent->customer_id;
            $modelData['customer_client_id'] = $parent->id;
        }
        $modelData['shop_id'] = $parent->shop_id;

        /** @var FulfilmentOrder $fulfilmentOrder */
        $fulfilmentOrder = $parent->shop->fulfilmentOrders()->create($modelData);
        $fulfilmentOrder->stats()->create();

        $deliveryAddress = StoreHistoricAddress::run($deliveryAddress);
        AttachHistoricAddressToModel::run($fulfilmentOrder, $deliveryAddress, ['scope'=>'delivery']);


        foreach ($items as $itemData) {
            StoreFulfilmentOrderItem::run(fulfilmentOrder: $fulfilmentOrder, modelData: $itemData);
        }
        HydrateFulfilmentOrder::make()->originalItems($fulfilmentOrder);


        return $fulfilmentOrder;
    }
}
