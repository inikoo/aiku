<?php

/*
 * author Arya Permana - Kirin
 * created on 11-02-2025-14h-10m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\PalletStoredItem;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\PalletStoredItem\PalletStoredItemStateEnum;
use App\Models\Fulfilment\PalletStoredItem;

class SetPalletStoredItemStateToReturned extends OrgAction
{
    use WithActionUpdate;

    public function handle(PalletStoredItem $palletStoredItem): PalletStoredItem
    {
        $this->update($palletStoredItem, [
            'state' => PalletStoredItemStateEnum::RETURNED
        ]);

        return $palletStoredItem;
    }
}
