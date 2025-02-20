<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 23-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\StoredItem;
use App\Models\Fulfilment\StoredItemAuditDelta;

class RepairStoredItem
{
    use WithActionUpdate;



    protected function handle(StoredItem $storedItem): StoredItem
    {

        $storedItemsDelta = StoredItemAuditDelta::where('stored_item_id', $storedItem->id)
            ->latest()
            ->first();

        if (!$storedItemsDelta) {
            print "No storedItemsDelta found -> {$storedItem->id}; ref: {$storedItem->reference}\n";

        } else {
            print "Delta found -> {$storedItem->id}; ref: {$storedItem->reference}\n";
        }

        return $storedItem;
    }

    public string $commandSignature = 'stored_items:debug_delta';

    public function asCommand($command): void
    {
        $storedItems = StoredItem::all();

        foreach ($storedItems as $storedItem) {
            $this->handle($storedItem);
        }
    }

}
