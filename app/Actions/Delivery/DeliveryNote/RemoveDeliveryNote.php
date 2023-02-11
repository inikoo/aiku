<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 Feb 2023 12:29:31 Malaysia Time, Ubud Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Delivery\DeliveryNote;


use App\Models\Delivery\DeliveryNote;

class RemoveDeliveryNote
{

    public function handle(DeliveryNote $deliveryNote): void
    {
        $deliveryNote->deliveryNoteItems()->forceDelete();
        $deliveryNote->forceDelete();
    }
}
