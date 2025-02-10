<?php

/*
 * author Arya Permana - Kirin
 * created on 10-02-2025-10h-48m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\StoredItemMovement;

use App\Actions\Fulfilment\PalletStoredItem\SetPalletStoredItemQuantity;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\PalletReturn\PalletReturnItemStateEnum;
use App\Enums\Fulfilment\StoredItemMovement\StoredItemMovementTypeEnum;
use App\Models\Fulfilment\PalletReturnItem;
use App\Models\Fulfilment\StoredItemMovement;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class StoreStoredItemMovementFromPicking extends OrgAction
{
    public function handle(PalletReturnItem $palletReturnItem, array $modelData): ?StoredItemMovement
    {
        $storedItemMovement = DB::transaction(function () use ($palletReturnItem, $modelData) {
            $quantity = Arr::pull($modelData, 'quantity');
            $palletStoredItem = $palletReturnItem->palletStoredItem;

            data_set($modelData, 'stored_item_id', $palletStoredItem->stored_item_id);
            data_set($modelData, 'pallet_id', $palletStoredItem->pallet_id);
            data_set($modelData, 'location_id', $palletStoredItem->pallet->location_id);

            data_set($modelData, 'pallet_return_item_id', $palletReturnItem->id);
            data_set($modelData, 'pallet_return_id', $palletReturnItem->pallet_return_id);

            data_set($modelData, 'type', StoredItemMovementTypeEnum::PICKED);
            data_set($modelData, 'quantity', -$quantity);
            data_set($modelData, 'moved_at', now());

            $storedItemMovement = StoredItemMovement::create($modelData);

            $palletReturnItem->update([
                'state' => PalletReturnItemStateEnum::PICKED
            ]);

            SetPalletStoredItemQuantity::run($palletStoredItem);

            return $storedItemMovement;
        });

        return $storedItemMovement;
    }
}
