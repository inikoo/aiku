<?php

/*
 * author Arya Permana - Kirin
 * created on 07-02-2025-16h-45m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\StoredItemMovement\StoreStoredItemMovementFromPicking;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Fulfilment\PalletReturnItemResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturnItem;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class SetPalletReturnWithStoredItemAsPicked extends OrgAction
{
    use WithActionUpdate;


    private PalletReturnItem $palletReturnItem;

    public function handle(PalletReturnItem $palletReturnItem): PalletReturnItem
    {
        StoreStoredItemMovementFromPicking::run($palletReturnItem, $palletReturnItem->palletStoredItem);

        return $palletReturnItem;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo("fulfilment.{$this->fulfilment->id}.edit");
    }

    // public function fromRetina(PalletReturnItem $palletReturnItem, ActionRequest $request): PalletReturnItem
    // {
    //     /** @var FulfilmentCustomer $fulfilmentCustomer */
    //     $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
    //     $this->fulfilment   = $fulfilmentCustomer->fulfilment;
    //     $this->pallet       = $palletReturnItem;

    //     $this->initialisation($request->get('website')->organisation, $request);

    //     return $this->handle($palletReturnItem);
    // }

    public function asController(PalletReturnItem $palletReturnItem, ActionRequest $request): PalletReturnItem
    {
        $this->palletReturnItem = $palletReturnItem;
        $this->initialisationFromFulfilment($palletReturnItem->palletReturn->fulfilment, $request);

        return $this->handle($palletReturnItem);
    }


    // public function fromApi(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, PalletReturnItem $palletReturnItem, ActionRequest $request): PalletReturnItem
    // {
    //     $this->pallet = $palletReturnItem;
    //     $this->initialisationFromFulfilment($palletReturnItem->palletReturn->fulfilment, $request);

    //     return $this->handle($palletReturnItem);
    // }

    // public function action(PalletReturnItem $palletReturnItem, array $modelData, int $hydratorsDelay = 0): PalletReturnItem
    // {
    //     $this->pallet         = $palletReturnItem;
    //     $this->asAction       = true;
    //     $this->hydratorsDelay = $hydratorsDelay;
    //     $this->initialisationFromFulfilment($palletReturnItem->palletReturn->fulfilment, $modelData);

    //     return $this->handle($palletReturnItem);
    // }

    public function jsonResponse(PalletReturnItem $palletReturnItem): PalletReturnItemResource
    {
        return new PalletReturnItemResource($palletReturnItem);
    }
}
