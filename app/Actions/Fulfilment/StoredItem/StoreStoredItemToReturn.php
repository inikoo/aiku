<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Aug 2024 16:54:48 Central Indonesia Time, Bali Office, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydratePallets;
use App\Actions\OrgAction;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\PalletReturnItem;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsCommand;

class StoreStoredItemToReturn extends OrgAction
{
    use AsCommand;

    private PalletReturn $parent;

    public function handle(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        $reference = Arr::get($modelData, 'reference');
        $storedItem = StoredItem::where('reference', $reference)
                    ->where('fulfilment_customer_id', $palletReturn->fulfilment_customer_id)
                    ->first();
        $currentQuantity  = 0;
        $pallets           = $storedItem->pallets;
        $requiredQuantity  = Arr::get($modelData, 'quantity');

        foreach ($pallets as $pallet) {
            $remainingQuantity   = $requiredQuantity - $currentQuantity;
            $palletStoredItemQty = $pallet->storedItems->sum('pivot.quantity');

            if ($palletStoredItemQty <= $remainingQuantity) {
                $currentQuantity += $palletStoredItemQty;
            } else {
                $partialPallet           = clone $pallet;
                $partialPallet->quantity = $remainingQuantity;
                $currentQuantity += $remainingQuantity;
            }

            $this->attach($palletReturn, $pallet, $storedItem, $currentQuantity);

            if ($currentQuantity == $requiredQuantity) {
                break;
            }
        }

        $palletReturn->refresh();

        PalletReturnHydratePallets::run($palletReturn);

        return $palletReturn;
    }

    public function attach(PalletReturn $palletReturn, Pallet $pallet, StoredItem $value, $currentQuantity): void
    {
        $exists = $value->palletReturns()
            ->wherePivot('pallet_return_id', $palletReturn->id)
            ->wherePivot('stored_item_id', $value->id)
            ->exists();

        if (!$exists) {
            $value->palletReturns()->attach($palletReturn->id, [
                'stored_item_id'       => $value->id,
                'pallet_id'            => $pallet->id,
                'pallet_stored_item_id'=> $pallet->pivot->id,
                'quantity_ordered'     => $currentQuantity,
                'type'                 => 'StoredItem'
            ]);
        } else {
            $value->palletReturns()->updateExistingPivot($palletReturn->id, [
                'quantity_ordered'     => $currentQuantity,
                'type'                 => 'StoredItem'
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'quantity'  => ['required', 'numeric', 'min:0'],
            'reference' => [
                'required',
                'string',
                Rule::exists('stored_items', 'reference')->where(function ($query) {
                    $query->where('fulfilment_customer_id', $this->parent->fulfilment_customer_id);
                })
            ],
        ];
    }


    public function action(PalletReturn $palletReturn, array $modelData, int $hydratorsDelay = 0): PalletReturn
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->parent         = $palletReturn;
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $modelData);

        return $this->handle($palletReturn, $this->validatedData);
    }


}
