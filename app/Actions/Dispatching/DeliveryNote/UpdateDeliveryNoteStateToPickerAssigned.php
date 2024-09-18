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
use App\Enums\Dispatching\Picking\PickingOutcomeEnum;
use App\Enums\Dispatching\Picking\PickingStateEnum;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Dispatching\DeliveryNoteItem;
use App\Models\Dispatching\Picking;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateDeliveryNoteStateToPickerAssigned extends OrgAction
{
    use WithActionUpdate;

    private DeliveryNote $deliveryNote;

    public function handle(DeliveryNote $deliveryNote): DeliveryNote
    {
        data_set($modelData, 'assigned_at', now());
        data_set($modelData, 'state', DeliveryNoteStateEnum::PICKED->value);

        return $this->update($deliveryNote, $modelData);
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $deliveryNoteItems = $this->deliveryNote->deliveryNoteItems;

        foreach ($deliveryNoteItems as $deliveryNoteItem) {
            if (!$deliveryNoteItem->pickings || !$deliveryNoteItem->pickings->picker_id) {
                abort(403, 'All pickings must have a picker_id.');
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
