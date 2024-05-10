<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\DeliveryNote;

use App\Actions\Dispatch\DeliveryNote\Hydrators\DeliveryNoteHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Dispatch\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatch\DeliveryNote\DeliveryNoteStatusEnum;
use App\Http\Resources\Delivery\DeliveryNoteResource;
use App\Models\Dispatch\DeliveryNote;
use App\Rules\IUnique;
use Illuminate\Validation\Rules\Enum;

class UpdateDeliveryNote extends OrgAction
{
    use WithActionUpdate;


    private DeliveryNote $deliveryNote;

    public function handle(DeliveryNote $deliveryNote, array $modelData): DeliveryNote
    {
        $deliveryNote = $this->update($deliveryNote, $modelData, ['data']);
        DeliveryNoteHydrateUniversalSearch::dispatch($deliveryNote);

        return $deliveryNote;
    }

    public function rules(): array
    {
        return [
            'number' => [
                'sometimes',
                'string',
                'max:64',
                new IUnique(
                    table: 'delivery_notes',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->organisation->id],
                        ['column' => 'id', 'value' => $this->deliveryNote->id, 'operator' => '!=']
                    ]
                ),
            ],
            'state'  => ['sometimes', 'required', new Enum(DeliveryNoteStateEnum::class)],
            'status' => ['sometimes', 'required', new Enum(DeliveryNoteStatusEnum::class)],
            'email'  => ['sometimes', 'nullable', 'string', 'email'],
            'phone'  => ['sometimes', 'nullable', 'string'],
            'date'   => ['sometimes', 'date']
        ];
    }

    public function action(DeliveryNote $deliveryNote, array $modelData, int $hydratorsDelay = 0): DeliveryNote
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->deliveryNote   = $deliveryNote;

        $this->initialisationFromShop($deliveryNote->shop, $modelData);

        return $this->handle($deliveryNote, $this->validatedData);
    }

    public function jsonResponse(DeliveryNote $deliveryNote): DeliveryNoteResource
    {
        return new DeliveryNoteResource($deliveryNote);
    }
}
