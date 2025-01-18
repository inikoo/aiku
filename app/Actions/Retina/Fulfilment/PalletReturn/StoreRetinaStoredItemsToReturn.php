<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydratePallets;
use App\Actions\RetinaAction;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\PalletReturnItem;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsCommand;

class StoreRetinaStoredItemsToReturn extends RetinaAction
{
    use AsCommand;

    public $commandSignature = 'stored-item:store-to-return {palletReturn}';
    private PalletReturn $parent;

    public function handle(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        $storedItemModels = Arr::get($modelData, 'stored_items');

        if (blank($storedItemModels)) {
            PalletReturnItem::where('pallet_return_id', $palletReturn->id)->delete();
            return $palletReturn;
        }

        PalletReturnItem::where('pallet_return_id', $palletReturn->id)
            ->whereNotIn('stored_item_id', array_keys($storedItemModels))->delete();

        $storedItems = $palletReturn->fulfilmentCustomer->storedItems()
            ->whereIn('stored_items.id', array_keys($storedItemModels))
            ->get();

        foreach ($storedItems as $storedItem) {
            $pallets                   = $storedItem->pallets;
            $requiredQuantity          = Arr::get($storedItemModels, $storedItem->id)['quantity'];
            $allocatedQuantity         = 0;
            $existingPalletReturnItems = PalletReturnItem::where('pallet_return_id', $palletReturn->id)
            ->where('stored_item_id', $storedItem->id)
            ->exists();

            if ($existingPalletReturnItems) {
                $this->deleteItems($palletReturn, $storedItem, $allocatedQuantity);
            }

            foreach ($pallets as $pallet) {
                $palletStoredItemQty = $pallet->storedItems
                    ->where('pivot.stored_item_id', $storedItem->id)
                    ->first()->pivot->quantity ?? 0;

                if ($allocatedQuantity < $requiredQuantity) {
                    $quantityToUse = min($palletStoredItemQty, $requiredQuantity - $allocatedQuantity);
                    $this->attach($palletReturn, $pallet, $storedItem, $quantityToUse);
                    $allocatedQuantity += $quantityToUse;
                }
            }

        }

        $palletReturn->refresh();

        PalletReturnHydratePallets::run($palletReturn);

        return $palletReturn;
    }

    public function attach(PalletReturn $palletReturn, Pallet $pallet, StoredItem $storedItem, $quantityToUse): void
    {
        $storedItem->palletReturns()->attach($palletReturn->id, [
            'stored_item_id'       => $storedItem->id,
            'pallet_id'            => $pallet->id,
            'pallet_stored_item_id' => $pallet->pivot->id,
            'quantity_ordered'     => $quantityToUse,
            'type'                 => 'StoredItem'
        ]);
    }

    protected function deleteItems(PalletReturn $palletReturn, StoredItem $storedItem, $allocatedQuantity): void
    {
        $existingPivotItems = PalletReturnItem::where('pallet_return_id', $palletReturn->id)
            ->where('stored_item_id', $storedItem->id)
            ->get();

        foreach ($existingPivotItems as $pivotItem) {
            $pivotItem->delete();
        }
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stored_items.*.quantity' => ['required', 'integer']
        ];
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $this->parent       = $palletReturn;

        $this->initialisation($request);
        return $this->handle($palletReturn, $this->validatedData);
    }

    public function htmlResponse(PalletReturn $palletReturn, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('retina.fulfilment.storage.pallet-returns.show', [
            'palletReturn'     => $palletReturn->slug
        ]);
    }
}
