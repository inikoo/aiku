<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\DeliveryNoteItem;

use App\Models\Dispatch\DeliveryNote;
use App\Models\Dispatch\DeliveryNoteItem;
use App\Models\Dispatch\Shipment;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreDeliveryNoteItem
{
    use AsAction;
    use WithAttributes;

    public function handle(DeliveryNote $deliveryNote, array $modelData): DeliveryNoteItem
    {
        /** @var \App\Models\Dispatch\DeliveryNoteItem $deliveryNoteItem */
        $deliveryNoteItem = $deliveryNote->deliveryNoteItems()->create($modelData);

        return $deliveryNoteItem;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'unique:tenant.delivery_note_items', 'between:2,9', 'alpha'],
            'name' => ['required', 'max:250', 'string']
        ];
    }

    public function action(DeliveryNote $deliveryNote, array $modelData): DeliveryNoteItem
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($deliveryNote, $validatedData);
    }
}
