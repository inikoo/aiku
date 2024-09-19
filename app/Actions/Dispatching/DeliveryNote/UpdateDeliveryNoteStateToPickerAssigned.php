<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\DeliveryNote;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Models\Dispatching\DeliveryNote;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class UpdateDeliveryNoteStateToPickerAssigned extends OrgAction
{
    use WithActionUpdate;

    private DeliveryNote $deliveryNote;

    public function handle(DeliveryNote $deliveryNote): DeliveryNote
    {
        data_set($modelData, 'picker_assigned_at', now());
        data_set($modelData, 'state', DeliveryNoteStateEnum::PICKER_ASSIGNED->value);

        return $this->update($deliveryNote, $modelData);
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $deliveryNoteItems = $this->deliveryNote->deliveryNoteItems;

        foreach ($deliveryNoteItems as $deliveryNoteItem) {
            if (!$deliveryNoteItem->pickings || !$deliveryNoteItem->pickings->picker_id) {
                throw ValidationException::withMessages(['All items must have picker assigned']);
            }
        }
    }

    public function asController(DeliveryNote $deliveryNote, ActionRequest $request): DeliveryNote
    {
        $this->deliveryNote = $deliveryNote;
        $this->initialisationFromShop($deliveryNote->shop, $request);

        return $this->handle($deliveryNote);
    }
}
