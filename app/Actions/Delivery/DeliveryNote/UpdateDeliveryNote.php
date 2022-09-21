<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 05 Dec 2021 15:42:26 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Delivery\DeliveryNote;

use App\Actions\Helpers\Address\StoreImmutableAddress;
use App\Actions\WithActionUpdate;
use App\Models\Delivery\DeliveryNote;
use App\Models\Helpers\Address;

class UpdateDeliveryNote
{
    use WithActionUpdate;

    public function handle(DeliveryNote $deliveryNote, array $modelData, Address $deliveryAddress): DeliveryNote {

        $deliveryAddress = StoreImmutableAddress::run($deliveryAddress);

        $modelData['delivery_address_id'] = $deliveryAddress->id;

        return $this->update($deliveryNote, $modelData, ['data']);


    }
}
