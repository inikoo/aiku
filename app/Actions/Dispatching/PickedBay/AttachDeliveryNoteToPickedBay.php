<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 19 Feb 2026 12:14:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\PickedBay;

use App\Actions\Dispatching\DeliveryNote\Hydrators\DeliveryNoteHydratePickedBays;
use App\Actions\Dispatching\PickedBay\Hydrators\PickedBayHydrateNumberDeliveryNotes;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Inventory\PickedBay;

class AttachDeliveryNoteToPickedBay extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;


    public function handle(PickedBay $pickedBay, DeliveryNote $deliveryNote): void
    {
        if (in_array($deliveryNote->state, [
            DeliveryNoteStateEnum::PACKING,
            DeliveryNoteStateEnum::PACKED,
            DeliveryNoteStateEnum::FINALISED,
            DeliveryNoteStateEnum::DISPATCHED,
            DeliveryNoteStateEnum::CANCELLED,
        ], true)) {
            abort(422, __('This delivery note has already left picking, it can not be put in a picked bay'));
        }

        $pickedBay->deliveryNotes()->syncWithoutDetaching([
            $deliveryNote->id => [
                'group_id'        => $deliveryNote->group_id,
                'organisation_id' => $deliveryNote->organisation_id
            ]
        ]);
        PickedBayHydrateNumberDeliveryNotes::run($pickedBay->id);
        DeliveryNoteHydratePickedBays::dispatch($deliveryNote->id);
    }


}
