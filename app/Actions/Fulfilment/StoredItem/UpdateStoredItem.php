<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\Fulfilment\StoredItem\Hydrators\StoredItemHydrateUniversalSearch;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\StoredItem\StoredItemTypeEnum;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateStoredItem
{
    use WithActionUpdate;

    public function handle(StoredItem $storedItem, array $modelData): StoredItem
    {
        $storedItem =  $this->update($storedItem, $modelData, ['data']);

        StoredItemHydrateUniversalSearch::dispatch($storedItem);
        return $storedItem;
    }


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("hr.edit");
    }

    public function rules(): array
    {
        return [
            'reference' => ['sometimes', 'required', 'unique:tenant.stored_items', 'between:2,9', 'alpha'],
            'type' => ['sometimes', 'required', Rule::in(StoredItemTypeEnum::values())]
        ];
    }

    public function asController(StoredItem $storedItem, ActionRequest $request): StoredItem
    {
        $request->validate();
        return $this->handle($storedItem, $request->validated());
    }


    public function jsonResponse(StoredItem $storedItem): StoredItemResource
    {
        return new StoredItemResource($storedItem);
    }
}
