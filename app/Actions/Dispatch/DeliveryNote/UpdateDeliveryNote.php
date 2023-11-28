<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\DeliveryNote;

use App\Actions\Dispatch\DeliveryNote\Hydrators\DeliveryNoteHydrateUniversalSearch;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Dispatch\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatch\DeliveryNote\DeliveryNoteStatusEnum;
use App\Http\Resources\Delivery\DeliveryNoteResource;
use App\Models\Dispatch\DeliveryNote;
use Illuminate\Validation\Rules\Enum;

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
            'number' => ['required', 'unique:delivery_notes', 'numeric'],
            'state'  => ['sometimes', 'required', new Enum(DeliveryNoteStateEnum::class)],
            'status' => ['sometimes', 'required', new Enum(DeliveryNoteStatusEnum::class)],
            'email'  => ['required', 'string', 'email'],
            'phone'  => ['required', 'string'],
            'date'   => ['required', 'date']
        ];
    }

    public function action(DeliveryNote $deliveryNote, array $objectData): DeliveryNote
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($deliveryNote, $validatedData);
    }

    public function jsonResponse(DeliveryNote $deliveryNote): DeliveryNoteResource
    {
        return new DeliveryNoteResource($deliveryNote);
    }
}
