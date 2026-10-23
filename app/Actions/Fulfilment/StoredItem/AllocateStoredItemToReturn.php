<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 22 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\PalletStoredItem;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

class AllocateStoredItemToReturn
{
    use AsObject;

    /**
     * @return Collection<int, PalletStoredItem>
     */
    public function handle(PalletReturn $palletReturn, StoredItem $storedItem, float $quantity): Collection
    {
        $palletStoredItems = $this->palletStoredItems($palletReturn, $storedItem);

        $quantities = $palletStoredItems->mapWithKeys(fn (PalletStoredItem $palletStoredItem) => [$palletStoredItem->id => 0.0])->all();
        $left       = $quantity;

        foreach ($palletStoredItems as $palletStoredItem) {
            $take                             = min($left, $palletStoredItem->getMaxQuantityForReturn($palletReturn));
            $quantities[$palletStoredItem->id] = $take;
            $left                             -= $take;
        }

        if ($left > 0 && $palletStoredItems->isNotEmpty()) {
            $quantities[$palletStoredItems->first()->id] += $left;
        }

        foreach ($palletStoredItems as $palletStoredItem) {
            AttachStoredItemToReturn::run($palletReturn, $palletStoredItem, ['quantity_ordered' => $quantities[$palletStoredItem->id]]);
        }

        return $palletStoredItems->filter(fn (PalletStoredItem $palletStoredItem) => $quantities[$palletStoredItem->id] > 0)->values();
    }

    public static function freeQuantity(PalletReturn $palletReturn, StoredItem $storedItem): float
    {
        return (float) self::make()->palletStoredItems($palletReturn, $storedItem)
            ->sum(fn (PalletStoredItem $palletStoredItem) => $palletStoredItem->getMaxQuantityForReturn($palletReturn));
    }

    /**
     * @return Collection<int, PalletStoredItem>
     */
    public function palletStoredItems(PalletReturn $palletReturn, StoredItem $storedItem): Collection
    {
        $palletStoredItemIdsInReturn = $palletReturn->items()
            ->where('stored_item_id', $storedItem->id)
            ->pluck('pallet_stored_item_id')
            ->all();

        return $storedItem->palletStoredItems()
            ->where(fn ($query) => $query->where('quantity', '>', 0)->orWhereIn('id', $palletStoredItemIdsInReturn))
            ->orderBy('id')
            ->get()
            ->sortBy(fn (PalletStoredItem $palletStoredItem) => [
                in_array($palletStoredItem->id, $palletStoredItemIdsInReturn) ? 0 : 1,
                -$palletStoredItem->getMaxQuantityForReturn($palletReturn),
                $palletStoredItem->id,
            ])
            ->values();
    }
}
