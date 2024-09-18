<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\DeliveryNote;

use App\Actions\Dispatching\Picking\UpdatePickingStateToPacking;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Models\Dispatching\DeliveryNote;
use Lorisleiva\Actions\ActionRequest;

class UpdateDeliveryNoteStateToPacking extends OrgAction
{
    use WithActionUpdate;

    public function handle(DeliveryNote $deliveryNote): DeliveryNote
    {
        $deliveryNoteItems = $deliveryNote->deliveryNoteItems;

        foreach($deliveryNoteItems as $deliveryNoteItem) {
            UpdatePickingStateToPacking::make()->action($deliveryNoteItem->pickings, []);
        }
        data_set($modelData, 'packing_at', now());
        data_set($modelData, 'state', DeliveryNoteStateEnum::PACKING->value);

        return $this->update($deliveryNote, $modelData);
    }

    public function asController(DeliveryNote $deliveryNote, ActionRequest $request): DeliveryNote
    {
        $this->initialisationFromShop($deliveryNote->shop, $request);

        return $this->handle($deliveryNote);
    }
}
