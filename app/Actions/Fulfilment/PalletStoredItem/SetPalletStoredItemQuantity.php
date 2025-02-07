<?php

/*
 * author Arya Permana - Kirin
 * created on 06-02-2025-10h-56m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\PalletStoredItem;

use App\Actions\Fulfilment\StoredItem\SetStoredItemQuantity;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\StoredItemAuditDelta\StoredItemAuditDeltaTypeEnum;
use App\Enums\Fulfilment\StoredItemMovement\StoredItemMovementTypeEnum;
use App\Models\Fulfilment\PalletStoredItem;
use App\Models\Fulfilment\StoredItemAuditDelta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsCommand;

class SetPalletStoredItemQuantity
{
    use WithActionUpdate;
    use AsCommand;

    public string $commandSignature = 'pallet_stored_item:set_quantity {palletStoredItem?}';
    public function handle(PalletStoredItem $palletStoredItem)
    {
        $quantity = $this->processQuantity($palletStoredItem);

        SetStoredItemQuantity::run($palletStoredItem->storedItem);

        return $quantity;
    }
    public function processQuantity(PalletStoredItem $palletStoredItem)
    {
        if ($palletStoredItem->in_process) {
            return $palletStoredItem->quantity;
        }

        $delta = StoredItemAuditDelta::where('pallet_id', $palletStoredItem->pallet_id)
                ->where('stored_item_id', $palletStoredItem->stored_item_id)
                ->latest()
                ->first();

        if (!$delta) {
            return 0;
        }

        
        if($delta->audit_type == StoredItemAuditDeltaTypeEnum::DELIVERY) {
            $quantityMovements = DB::table('stored_item_movements')->where('pallet_id', $palletStoredItem->pallet_id)
            ->where('stored_item_id', $palletStoredItem->stored_item_id)
            ->whereNotIn('type', [StoredItemMovementTypeEnum::AUDIT_ADDITION, StoredItemMovementTypeEnum::AUDIT_SUBTRACTION])
            ->sum('quantity');

            $deltaQuantity = $delta->audited_quantity;

            if($quantityMovements == $deltaQuantity) {
                $quantity = $deltaQuantity;
            }
        } else {
            $quantityMovements = DB::table('stored_item_movements')->where('pallet_id', $palletStoredItem->pallet_id)
            ->where('stored_item_id', $palletStoredItem->stored_item_id)
            ->sum('quantity');
            
            $deltaQuantity = $delta->audited_quantity;

            if($quantityMovements == $deltaQuantity) {
                $quantity = $deltaQuantity;
            }
        }



        $this->update(
            $palletStoredItem,
            [
            'quantity' => $quantity
        ]
        );

        $palletStoredItem->refresh();

        return $quantity;
    }

    public function asCommand(Command $command): int
    {
        $palletStoredItem = $command->argument('palletStoredItem');
        $palletStoredItem = PalletStoredItem::where('id', $palletStoredItem)->first();

        if (!$palletStoredItem) {
            $palletStoredItems = PalletStoredItem::all();
            foreach ($palletStoredItems as $palletStoredItem) {
                $this->handle($palletStoredItem);
            }
            return 0;
        }
        $this->handle($palletStoredItem);

        return 0;
    }
}
