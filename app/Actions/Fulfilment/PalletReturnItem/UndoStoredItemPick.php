<?php

/*
 * author Arya Permana - Kirin
 * created on 10-02-2025-13h-08m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\PalletReturnItem;

use App\Actions\Fulfilment\PalletStoredItem\RunPalletStoredItemQuantity;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithFulfilmentAuthorisation;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\PalletReturn\PalletReturnItemStateEnum;
use App\Models\Fulfilment\PalletReturnItem;
use App\Models\Fulfilment\StoredItemMovement;
use Lorisleiva\Actions\ActionRequest;

class UndoStoredItemPick extends OrgAction
{
    use WithFulfilmentAuthorisation;
    use WithActionUpdate;


    public function handle(PalletReturnItem $palletReturnItem): PalletReturnItem
    {
        $this->update($palletReturnItem, [
            'state'           => PalletReturnItemStateEnum::PICKING,
            'quantity_picked' => 0
        ]);

        $movement = StoredItemMovement::where('pallet_return_item_id', $palletReturnItem->id)->first();
        $movement->delete();

        RunPalletStoredItemQuantity::run($palletReturnItem->palletStoredItem);

        return $palletReturnItem;
    }

    public function asController(PalletReturnItem $palletReturnItem, ActionRequest $request): PalletReturnItem
    {
        $this->initialisationFromFulfilment($palletReturnItem->palletReturn->fulfilment, $request);

        return $this->handle($palletReturnItem);
    }

    public function jsonResponse(PalletReturnItem $palletReturnItem, ActionRequest $request): PalletReturnItem
    {
       return $palletReturnItem;
    }
}
