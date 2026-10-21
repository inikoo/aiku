<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 22:20:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\PickedBay;

use App\Actions\Dispatching\DeliveryNote\Hydrators\DeliveryNoteHydratePickedBays;
use App\Actions\Dispatching\PickedBay\Hydrators\PickedBayHydrateNumberDeliveryNotes;
use App\Models\Dispatching\DeliveryNote;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class DetachDeliveryNoteFromPickedBays
{
    use AsAction;

    public function handle(DeliveryNote $deliveryNote): void
    {
        $pickedBayIds = DB::table('picked_bay_has_delivery_notes')
            ->where('delivery_note_id', $deliveryNote->id)
            ->pluck('picked_bay_id')
            ->unique();

        if ($pickedBayIds->isEmpty()) {
            return;
        }

        DB::table('picked_bay_has_delivery_notes')->where('delivery_note_id', $deliveryNote->id)->delete();

        foreach ($pickedBayIds as $pickedBayId) {
            PickedBayHydrateNumberDeliveryNotes::run($pickedBayId);
        }
        DeliveryNoteHydratePickedBays::dispatch($deliveryNote->id);
    }
}
