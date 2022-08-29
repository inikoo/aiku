<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 05 Dec 2021 15:42:26 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Delivery\DeliveryNote;

use App\Actions\Helpers\Address\StoreImmutableAddress;
use App\Models\Utils\ActionResult;
use App\Actions\WithUpdate;
use App\Models\Delivery\DeliveryNote;
use App\Models\Helpers\Address;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateDeliveryNote
{
    use AsAction;
    use WithUpdate;

    public function handle(
        DeliveryNote $deliveryNote,
        array $modelData,
        Address $deliveryAddress,
    ): ActionResult {
        $res = new ActionResult();

        $deliveryAddress = StoreImmutableAddress::run($deliveryAddress);

        $modelData['delivery_address_id'] = $deliveryAddress->id;
        $deliveryNote->update(Arr::except($modelData, ['data']));
        $deliveryNote->update($this->extractJson($modelData));

        return $this->postUpdate($res, $deliveryNote);
    }
}
