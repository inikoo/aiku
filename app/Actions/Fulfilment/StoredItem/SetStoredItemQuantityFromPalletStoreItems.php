<?php

/*
 * author Arya Permana - Kirin
 * created on 06-02-2025-11h-15m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\Fulfilment\PalletStoredItem\CalculatePalletStoredItemQuantity;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsCommand;

class SetStoredItemQuantityFromPalletStoreItems extends OrgAction
{

    public function handle(StoredItem $storedItem): StoredItem
    {
        $quantity = 0;
        foreach ($storedItem->pallets as $pallet) {
            $quantity = +$pallet->pivot->quantity;
        }

        $storedItem->update(
            [
                'total_quantity' => $quantity
            ]
        );

        return $storedItem;
    }

}
