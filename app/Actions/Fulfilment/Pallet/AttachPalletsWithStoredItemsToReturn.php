<?php

/*
 * author Arya Permana - Kirin
 * created on 07-02-2025-10h-24m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\Fulfilment\PalletReturn\AutoAssignServicesToPalletReturn;
use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydratePallets;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsCommand;

class AttachPalletsWithStoredItemsToReturn extends OrgAction
{
    use AsCommand;


    private PalletReturn $parent;

    public function handle(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        $selectedPalletIds = Arr::get($modelData, 'pallets', []);

        if (count($selectedPalletIds) === 0) {
            $allAttachedPalletIds = $palletReturn->pallets()->pluck('pallets.id')->toArray();
            $this->unselectPallets($palletReturn, $allAttachedPalletIds);
            return $palletReturn;
        }

        $palletReturnPalletIds = $palletReturn->pallets()->pluck('pallets.id')->toArray();

        $palletsToSelect   = array_diff($selectedPalletIds, $palletReturnPalletIds);  // Pallets not yet attached
        $palletsToUnselect = array_diff($palletReturnPalletIds, $selectedPalletIds); // Pallets to be unselected

        if (!empty($palletsToSelect)) {
            $storedItemsData = [];
            foreach ($palletsToSelect as $palletId) {
                $pallet = Pallet::find($palletId);
                foreach ($pallet->storedItems as $storedItem) {
                    $storedItemId = $storedItem->id;
                }
                $storedItemsData[$storedItemId] = [
                    'type'                 => 'StoredItem',
                    'pallet_id'            => $pallet->id,
                    'pallet_stored_item_id' => $storedItem->pivot->id,
                    'quantity_ordered'      => $storedItem->pivot->quantity
                ];
            }

            $palletReturn->storedItems()->syncWithoutDetaching($storedItemsData);

            Pallet::whereIn('id', $palletsToSelect)->update([
                'pallet_return_id' => $palletReturn->id,
                'status'           => PalletStatusEnum::RETURNING,
                'state'            => PalletStateEnum::REQUEST_RETURN_IN_PROCESS,
                'requested_for_return_at' => now()
            ]);

            $pallets = Pallet::findOrFail($palletsToSelect);
            foreach ($pallets as $pallet) {
                AutoAssignServicesToPalletReturn::run($palletReturn, $pallet);
            }
        }

        if (!empty($palletsToUnselect)) {
            $this->unselectPallets($palletReturn, $palletsToUnselect);
        }

        // Refresh the pallet return after any changes
        $palletReturn->refresh();

        PalletReturnHydratePallets::run($palletReturn);

        return $palletReturn;
    }

    public function unselectPallets(PalletReturn $palletReturn, array $palletIds): void
    {
        Pallet::whereIn('id', $palletIds)->update([
            'pallet_return_id' => null,
            'status'           => PalletStatusEnum::STORING,
            'state'            => PalletStateEnum::STORING,
            'requested_for_return_at' => null
        ]);

        $storedItemIds = [];

        foreach ($palletReturn->pallets as $pallet) {
            foreach ($pallet->storedItems as $storedItem) {
                $storedItemIds[] = $storedItem->id;
            }
        }

        $palletReturn->storedItems()->detach($storedItemIds);

        foreach ($palletIds as $palletId) {
            $pallet = Pallet::find($palletId);

            if ($pallet) {
                AutoAssignServicesToPalletReturn::run($palletReturn, $pallet);
            }
        }
        PalletReturnHydratePallets::run($palletReturn);
        $palletReturn->refresh();
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            // TODO: Raul please do the permission for the web user
            return true;
        }

        return $request->user()->authTo("fulfilment.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        return [
            'pallets' => ['sometimes', 'array']
        ];
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->parent = $palletReturn;
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $request);

        return $this->handle($palletReturn, $this->validatedData);
    }

    public function fromRetina(PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $this->parent       = $palletReturn;
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment   = $fulfilmentCustomer->fulfilment;

        $this->initialisation($request->get('website')->organisation, $request);
        return $this->handle($palletReturn, $this->validatedData);
    }

    public function action(PalletReturn $palletReturn, array $modelData, int $hydratorsDelay = 0): PalletReturn
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->parent         = $palletReturn;
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $modelData);

        return $this->handle($palletReturn, $this->validatedData);
    }




    public function htmlResponse(PalletReturn $palletReturn, ActionRequest $request): void
    {
        // $routeName = $request->route()->getName();

        // return match ($routeName) {
        //     'grp.models.pallet-return.pallet.store' => Redirect::route('grp.org.fulfilments.show.crm.customers.show.pallet_returns.show', [
        //         'organisation'           => $palletReturn->organisation->slug,
        //         'fulfilment'             => $palletReturn->fulfilment->slug,
        //         'fulfilmentCustomer'     => $palletReturn->fulfilmentCustomer->slug,
        //         'palletReturn'           => $palletReturn->slug
        //     ]),
        //     default => Redirect::route('retina.fulfilment.storage.pallet_returns.show', [
        //         'palletReturn'     => $palletReturn->slug
        //     ])
        // };
    }
}
