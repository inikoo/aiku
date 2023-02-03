<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 31 Jan 2023 20:16:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Delivery\DeliveryNoteItem;

use App\Models\Delivery\DeliveryNote;
use App\Models\Delivery\DeliveryNoteItem;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreDeliveryNoteItem
{
    use AsAction;

    public function handle(DeliveryNote $deliveryNote, array $modelData): DeliveryNoteItem
    {



        /** @var \App\Models\Delivery\DeliveryNoteItem $deliveryNoteItem */
        $deliveryNoteItem = $deliveryNote->deliveryNoteItems()->create($modelData);


        return $deliveryNoteItem;
    }
}


