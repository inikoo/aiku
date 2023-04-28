<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\DeliveryNote;

use App\Actions\Dispatch\DeliveryNote\Hydrators\DeliveryNoteHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Helpers\Address;
use App\Models\Sales\Order;

class UpdateDeliveryNote
{
    use WithActionUpdate;

    public function handle(DeliveryNote $deliveryNote, array $modelData): DeliveryNote
    {
        $deliveryNote = $this->update($deliveryNote, $modelData, ['data']);
        DeliveryNoteHydrateUniversalSearch::dispatch($deliveryNote);
        return $deliveryNote;
    }

    public function rules(): array
    {
        return [
            'number' => ['required', 'unique:tenant.delivery_notes', 'numeric'],
            'state' => ['required', 'max:250', 'string'],
            'status' => ['required', 'boolean'],
            'email' => ['required', 'string', 'email'],
            'phone' => ['required', 'string'],
            'date' => ['required', 'date'],
        ];
    }

    public function action(DeliveryNote $deliveryNote, array $objectData): DeliveryNote
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($deliveryNote, $validatedData);
    }
}
