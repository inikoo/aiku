<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\Fulfilment\StoredItem\Search\StoredItemRecordSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\StoredItem\StoredItemTypeEnum;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateStoredItem extends OrgAction
{
    use WithActionUpdate;

    public function handle(StoredItem $storedItem, array $modelData): StoredItem
    {
        $storedItem =  $this->update($storedItem, $modelData, ['data']);

        StoredItemRecordSearch::dispatch($storedItem);
        return $storedItem;
    }


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        return [
            'reference'   => ['sometimes', 'required', 'unique:stored_items', 'max:24', 'alpha'],
            'type'        => ['sometimes', 'required', Rule::enum(StoredItemTypeEnum::class)],
            'location_id' => ['sometimes', 'exists:locations,id']
        ];
    }

    public function asController(StoredItem $storedItem, ActionRequest $request): StoredItem
    {
        $this->initialisationFromFulfilment($storedItem->fulfilment, $request);
        return $this->handle($storedItem, $this->validatedData);
    }

    public function jsonResponse(StoredItem $storedItem): StoredItemResource
    {
        return new StoredItemResource($storedItem);
    }
}
