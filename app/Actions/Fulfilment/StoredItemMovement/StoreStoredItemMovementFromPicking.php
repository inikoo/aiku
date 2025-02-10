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
use App\Enums\Fulfilment\StoredItemMovement\StoredItemMovementTypeEnum;
use App\Models\Fulfilment\PalletReturnItem;
use App\Models\Fulfilment\PalletStoredItem;
use App\Models\Fulfilment\StoredItemMovement;

class StoreStoredItemMovementFromPicking extends OrgAction
{
    public function handle(PalletReturnItem $palletReturnItem, PalletStoredItem $palletStoredItem): ?StoredItemMovement
    {
        data_set($modelData, 'stored_item_id', $palletStoredItem->stored_item_id);
        data_set($modelData, 'pallet_id', $palletStoredItem->pallet_id);
        data_set($modelData, 'location_id', $palletStoredItem->pallet->location_id);

        data_set($modelData, 'pallet_return_item_id', $palletReturnItem->id);
        data_set($modelData, 'pallet_return_id', $palletReturnItem->pallet_return_id);

        data_set($modelData, 'type', StoredItemMovementTypeEnum::PICKED);
        data_set($modelData, 'quantity', 0 - $palletReturnItem->quantity_ordered);
        data_set($modelData, 'moved_at', now());

        $storedItemMovement = StoredItemMovement::create($modelData);

        SetPalletStoredItemQuantity::run($palletStoredItem);
        return $storedItemMovement;
    }
}
