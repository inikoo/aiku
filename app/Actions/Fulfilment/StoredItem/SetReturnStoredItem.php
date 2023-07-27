<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateStoredItems;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\StoredItem\StoredItemStateEnum;
use App\Enums\Fulfilment\StoredItem\StoredItemStatusEnum;
use App\Enums\Fulfilment\StoredItem\StoredItemTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SetReturnStoredItem
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(StoredItem $storedItem): StoredItem
    {
        $this->update($storedItem, [
            'status' => StoredItemStatusEnum::RETURNED
        ]);

        return $storedItem;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment.edit");
    }

    public function asController(StoredItem $storedItem): StoredItem
    {
        return $this->handle($storedItem);
    }

    public function htmlResponse(StoredItem $storedItem, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('fulfilment.stored-items.show', $storedItem->slug);
    }
}
