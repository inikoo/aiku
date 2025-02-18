<?php

/*
 * author Arya Permana - Kirin
 * created on 10-02-2025-13h-08m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\PalletReturnItem;

use App\Actions\Fulfilment\PalletReturn\AutomaticallySetPalletReturnAsPickedIfAllItemsPicked;
use App\Actions\Fulfilment\PalletStoredItem\SetPalletStoredItemStateToReturned;
use App\Actions\Fulfilment\StoredItemMovement\StoreStoredItemMovementFromPicking;
use App\Actions\Fulfilment\UI\WithFulfilmentAuthorisation;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\PalletReturnItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;

class PickPalletReturnItem extends OrgAction
{
    use WithFulfilmentAuthorisation;
    use WithActionUpdate;


    public function handle(PalletReturnItem $palletReturnItem, array $modelData): PalletReturnItem
    {
        return DB::transaction(function () use ($palletReturnItem, $modelData) {
            $quantity = Arr::get($modelData, 'quantity_picked');
            $palletStoredItemQuant = $palletReturnItem->palletStoredItem->quantity;
            $this->update($palletReturnItem, $modelData);

            StoreStoredItemMovementFromPicking::run($palletReturnItem, [
                'quantity' => $quantity
            ]);

            if ($quantity == $palletStoredItemQuant) {
                SetPalletStoredItemStateToReturned::run($palletReturnItem->palletStoredItem);
            }

            AutomaticallySetPalletReturnAsPickedIfAllItemsPicked::run($palletReturnItem->palletReturn);

            return $palletReturnItem;
        });
    }

    public function rules(): array
    {
        return [
            'quantity_picked'       => ['sometimes', 'numeric', 'min:0'],
        ];
    }

    public function asController(PalletReturnItem $palletReturnItem, ActionRequest $request): PalletReturnItem
    {
        $this->initialisationFromFulfilment($palletReturnItem->palletReturn->fulfilment, $request);

        return $this->handle($palletReturnItem, $this->validatedData);
    }
}
