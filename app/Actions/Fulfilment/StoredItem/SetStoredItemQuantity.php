<?php

/*
 * author Arya Permana - Kirin
 * created on 06-02-2025-11h-15m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\Fulfilment\PalletStoredItem\SetPalletStoredItemQuantity;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsCommand;

class SetStoredItemQuantity extends OrgAction
{
    use WithActionUpdate;

    public function handle(StoredItem $storedItem)
    {
        $quantity = 0;
        foreach($storedItem->pallets as $pallet) {
            $quantity =+ $pallet->pivot->quantity;
        }
        
        $storedItem = $this->update($storedItem, [
            'total_quantity' => $quantity
        ]);

        return $storedItem;
    }

    public string $commandSignature = 'stored_item:set_quantity {storedItem?}';
    public function asCommand(Command $command): int
    {
        $storedItem = $command->argument('storedItem');
        $storedItem = StoredItem::where('id', $storedItem)->first();

        if (!$storedItem) {
            $storedItems = StoredItem::all();
            foreach ($storedItems as $storedItem) {
                $this->handle($storedItem);

            }

            return 0;
        }

        $this->handle($storedItem);

        return 0;
    }
}
