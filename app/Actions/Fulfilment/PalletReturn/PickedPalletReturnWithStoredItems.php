<?php

/*
 * author Arya Permana - Kirin
 * created on 07-02-2025-16h-48m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydratePalletReturns;
use App\Actions\Dropshipping\CustomerSalesChannel\Hydrators\CustomerSalesChannelsHydrateFulfilmentOrders;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydratePalletReturns;
use App\Actions\Fulfilment\PalletReturn\Notifications\SendPalletReturnNotification;
use App\Actions\Fulfilment\StoredItemMovement\StoreStoredItemMovementFromPicking;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydratePalletReturns;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydratePalletReturns;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePalletReturns;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\PalletReturn\PalletReturnItemStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnTypeEnum;
use App\Http\Resources\Fulfilment\PalletReturnResource;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\PalletReturnItem;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class PickedPalletReturnWithStoredItems extends OrgAction
{
    use WithActionUpdate;


    public function handle(PalletReturn $palletReturn, array $modelData = []): PalletReturn
    {
        if ($palletReturn->type != PalletReturnTypeEnum::STORED_ITEM) {
            abort(419);
        }

        $this->ensurePalletsHoldTheStockLeftToPick($palletReturn);

        return DB::transaction(function () use ($palletReturn, $modelData) {
            $modelData[PalletReturnStateEnum::PICKED->value . '_at'] = now();
            $modelData['state'] = PalletReturnStateEnum::PICKED;

            $palletReturn = $this->update($palletReturn, $modelData);


            foreach ($palletReturn->storedItems as $storedItem) {


                $palletReturnItem = PalletReturnItem::find($storedItem->pivot->id);
                if ($palletReturnItem->state == PalletReturnItemStateEnum::PICKING) {
                    $quantityPicked = $palletReturnItem->quantity_ordered;
                    $palletReturnItem->update([
                        'quantity_picked' => $quantityPicked
                    ]);
                    StoreStoredItemMovementFromPicking::run($palletReturnItem, [
                        'quantity' => $quantityPicked
                    ]);

                }


            }
            SetStoredItemReturnAutoServices::run($palletReturn, true);
            GroupHydratePalletReturns::dispatch($palletReturn->group);
            OrganisationHydratePalletReturns::dispatch($palletReturn->organisation);
            WarehouseHydratePalletReturns::dispatch($palletReturn->warehouse);
            FulfilmentCustomerHydratePalletReturns::dispatch($palletReturn->fulfilmentCustomer);
            FulfilmentHydratePalletReturns::dispatch($palletReturn->fulfilment);

            if ($palletReturn->customerSalesChannel) {
                CustomerSalesChannelsHydrateFulfilmentOrders::dispatch($palletReturn->customerSalesChannel);
            }

            SendPalletReturnNotification::run($palletReturn);

            return $palletReturn;
        });
    }

    private function ensurePalletsHoldTheStockLeftToPick(PalletReturn $palletReturn): void
    {
        $shortItems = $palletReturn->items()
            ->where('state', PalletReturnItemStateEnum::PICKING)
            ->whereNotNull('pallet_stored_item_id')
            ->with(['palletStoredItem.pallet', 'storedItem'])
            ->get()
            ->filter(fn (PalletReturnItem $palletReturnItem) => (float) $palletReturnItem->quantity_ordered > max(0, (float) $palletReturnItem->palletStoredItem?->quantity));

        if ($shortItems->isEmpty()) {
            return;
        }

        throw ValidationException::withMessages([
            'message' => $shortItems->map(fn (PalletReturnItem $palletReturnItem) => __('Not enough stock of :reference in pallet :pallet: :stock left, :quantity to pick. Pick what is there and set the rest as not picked.', [
                'reference' => $palletReturnItem->storedItem?->reference,
                'pallet'    => $palletReturnItem->palletStoredItem?->pallet?->reference,
                'stock'     => max(0, (float) $palletReturnItem->palletStoredItem?->quantity) + 0,
                'quantity'  => (float) $palletReturnItem->quantity_ordered + 0,
            ]))->values()->all(),
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    // public function jsonResponse(PalletReturn $palletReturn): JsonResource
    // {
    //     return new PalletReturnResource($palletReturn);
    // }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $request);

        return $this->handle($palletReturn, $this->validatedData);
    }

    // public function action(FulfilmentCustomer $fulfilmentCustomer, PalletReturn $palletReturn): PalletReturn
    // {
    //     $this->asAction = true;
    //     $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, []);

    //     return $this->handle($palletReturn, $this->validatedData);
    // }
}
