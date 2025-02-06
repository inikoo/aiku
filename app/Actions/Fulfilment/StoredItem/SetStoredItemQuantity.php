<?php

/*
 * author Arya Permana - Kirin
 * created on 06-02-2025-11h-15m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\StoredItem;

class SetStoredItemQuantity extends OrgAction
{
    use WithActionUpdate;

    public function handle(StoredItem $storedItem)
    {
        $quantity = $storedItem->pallets->sum(function ($pallet) {
            return $pallet->pivot->quantity;
        });

        $storedItem = $this->update($storedItem, [
            'total_quantity' => $quantity
        ]);

        return $storedItem;
    }
}
