<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\Fulfilment\FulfilmentOrderItem\StoredItemHydrateUniversalSearch;
use App\Actions\Fulfilment\FulfilmentOrderItem\StoredItemResource;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\StoredItem;
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
            'code'          => ['sometimes','required'],

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
