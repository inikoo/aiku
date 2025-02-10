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

        $delta = StoredItemAuditDelta::where('stored_item_id', $storedItem->id)
            ->latest()
            ->first();

        if (!$delta) {
            print "No delta found -> {$storedItem->id}; ref: {$storedItem->reference}\n";
            return $storedItem;
        } else {
            print "Delta found -> {$storedItem->id}; ref: {$storedItem->reference}\n";
        }

        return $storedItem;
    }

    public string $commandSignature = 'stored_items:debug_delta';

    public function asCommand($command)
    {
        $storedItems = StoredItem::all();

        foreach ($storedItems as $storedItem) {
            $this->handle($storedItem);
        }
    }

}
