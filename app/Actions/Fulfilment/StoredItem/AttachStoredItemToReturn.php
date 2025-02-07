<?php

/*
 * author Arya Permana - Kirin
 * created on 07-02-2025-10h-24m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\OrgAction;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\PalletStoredItem;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class AttachStoredItemToReturn extends OrgAction
{
    private PalletStoredItem $palletStoredItem;

    public function handle(PalletReturn $palletReturn, PalletStoredItem $palletStoredItem, array $modelData): PalletReturn
    {
        $quantityOrdered = Arr::pull($modelData, 'quantity_ordered');
        if ($quantityOrdered == 0) {
            $palletReturn->storedItems()->detach($palletStoredItem->storedItem->id);
        } else {
            $palletReturn->storedItems()->attach(
                $palletStoredItem->storedItem->id,
                [
                    'type'                 => 'StoredItem',
                    'pallet_id'            => $palletStoredItem->pallet_id,
                    'pallet_stored_item_id' => $palletStoredItem->id,
                    'quantity_ordered'      => $quantityOrdered
                ]
            );
        }

        return $palletReturn;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo("fulfilment.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        return [
            'quantity_ordered' => ['required', 'numeric', 'min:0', 'max:'.$this->palletStoredItem->quantity]
        ];
    }

    public function asController(PalletReturn $palletReturn, PalletStoredItem $palletStoredItem, ActionRequest $request): PalletReturn
    {
        $this->palletStoredItem = $palletStoredItem;
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $request);

        return $this->handle($palletReturn, $palletStoredItem, $this->validatedData);
    }

    // public function fromRetina(PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    // {
    //     /** @var FulfilmentCustomer $fulfilmentCustomer */
    //     $this->parent       = $palletReturn;
    //     $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
    //     $this->fulfilment   = $fulfilmentCustomer->fulfilment;

    //     $this->initialisation($request->get('website')->organisation, $request);
    //     return $this->handle($palletReturn, $this->validatedData);
    // }

    public function action(PalletReturn $palletReturn, PalletStoredItem $palletStoredItem, array $modelData, int $hydratorsDelay = 0): PalletReturn
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->palletStoredItem = $palletStoredItem;
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $modelData);

        return $this->handle($palletReturn, $palletStoredItem, $this->validatedData);
    }
}
