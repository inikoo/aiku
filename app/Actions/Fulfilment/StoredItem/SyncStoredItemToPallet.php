<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SyncStoredItemToPallet
{
    use AsAction;
    use WithAttributes;

    public FulfilmentCustomer $fulfilmentCustomer;
    private Fulfilment $fulfilment;

    public function handle(Pallet $pallet, array $modelData): array
    {
        $storedItem = $pallet->storedItems()->syncWithPivotValues(Arr::get($modelData, 'stored_item_ids', []), [
            'quantity' => Arr::get($modelData, 'quantity', 1),
        ]);

        // hydrate stored items goes here

        return $storedItem;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        return [
            'stored_item_ids' => ['sometimes', 'array']
        ];
    }

    public function asController(Pallet $pallet, ActionRequest $request): array
    {
        $this->fulfilmentCustomer = $pallet->fulfilmentCustomer;
        $this->fulfilment         = $pallet->fulfilment;

        $this->setRawAttributes($request->all());

        return $this->handle($pallet, $this->validateAttributes());
    }

    public function htmlResponse(StoredItem $storedItem, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('grp.fulfilment.stored-items.show', $storedItem->slug);
    }
}
