<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\DeliveryNote;

use App\Actions\Dispatch\DeliveryNote\Hydrators\DeliveryNoteHydrateUniversalSearch;
use App\Actions\Helpers\Address\AttachHistoricAddressToModel;
use App\Actions\Helpers\Address\StoreHistoricAddress;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Helpers\Address;
use App\Models\Sales\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreDeliveryNote
{
    use AsAction;

    public function handle(
        Order $order,
        array $modelData,
        Address $seedDeliveryAddress,
    ): DeliveryNote {
        $modelData['shop_id']     = $order->shop_id;
        $modelData['customer_id'] = $order->customer_id;

        /** @var DeliveryNote $deliveryNote */
        $deliveryNote = $order->deliveryNotes()->create($modelData);
        $deliveryNote->stats()->create();

        $deliveryAddress = StoreHistoricAddress::run($seedDeliveryAddress);
        AttachHistoricAddressToModel::run($deliveryNote, $deliveryAddress, ['scope'=>'delivery']);

        DeliveryNoteHydrateUniversalSearch::dispatch($deliveryNote);
        return $deliveryNote;
    }
}
