<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\Fulfilment\StoredItem\Hydrators\StoredItemHydrateUniversalSearch;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\StoredItem\StoredItemTypeEnum;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Models\Fulfilment\Pallet;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdatePallet
{
    use WithActionUpdate;

    public function handle(Pallet $pallet, array $modelData): Pallet
    {
        $pallet =  $this->update($pallet, $modelData, ['data']);

        StoredItemHydrateUniversalSearch::dispatch($pallet);

        return $pallet;
    }


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }

    public function rules(): array
    {
        return [
            'reference'   => ['sometimes', 'required', 'unique:stored_items', 'between:2,9', 'alpha'],
            'type'        => ['sometimes', 'required', Rule::in(StoredItemTypeEnum::values())],
            'location_id' => ['required', 'exists:locations,id']
        ];
    }

    public function asController(Pallet $pallet, ActionRequest $request): Pallet
    {
        $mergedArray = array_merge($request->all(), [
            'location_id' => $request->input('location')['id']
        ]);
        $this->setRawAttributes($mergedArray);

        return $this->handle($pallet, $this->validateAttributes());
    }

    public function jsonResponse(Pallet $pallet): StoredItemResource
    {
        return new StoredItemResource($pallet);
    }
}
