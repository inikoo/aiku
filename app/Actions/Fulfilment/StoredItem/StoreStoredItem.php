<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateStoredItems;
use App\Enums\Fulfilment\StoredItem\StoredItemTypeEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreStoredItem
{
    use AsAction;
    use WithAttributes;

    public FulfilmentCustomer $fulfilmentCustomer;
    private Fulfilment $fulfilment;

    public function handle(FulfilmentCustomer|Pallet $parent, array $modelData): StoredItem
    {
        if($parent instanceof Pallet) {
            $modelData['type'] = StoredItemTypeEnum::PALLET;
        }

        /** @var StoredItem $storedItem */
        $storedItem = $parent->items()->create($modelData);

        if($parent instanceof FulfilmentCustomer) {
            FulfilmentCustomerHydrateStoredItems::dispatch($parent);
        }

        return $storedItem;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");
    }


    public function rules(): array
    {
        return [
            'reference'   => ['required', 'unique:stored_items', 'between:2,9', 'alpha'],
            'type'        => ['sometimes', Rule::in(StoredItemTypeEnum::values())],
            'location_id' => ['sometimes', 'exists:locations,id']
        ];
    }

    public function asController(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): StoredItem
    {
        $this->fulfilmentCustomer = $fulfilmentCustomer;
        $this->fulfilment         = $fulfilmentCustomer->fulfilment;

        $mergedArray              = array_merge($request->all(), [
            'location_id' => $request->input('location')['id']
        ]);
        $this->setRawAttributes($mergedArray);

        return $this->handle($fulfilmentCustomer, $this->validateAttributes());
    }

    public function inPallet(Pallet $pallet, ActionRequest $request): StoredItem
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
