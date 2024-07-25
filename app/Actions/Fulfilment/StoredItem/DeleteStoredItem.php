<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Models\Fulfilment\StoredItem;
use Lorisleiva\Actions\ActionRequest;

class DeleteStoredItem extends OrgAction
{
    use WithActionUpdate;

    public function handle(StoredItem $storedItem, array $modelData): StoredItem
    {
        $storedItem->delete();

        return $storedItem;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.edit");
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
