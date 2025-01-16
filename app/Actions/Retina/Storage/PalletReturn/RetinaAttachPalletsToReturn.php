<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Storage\PalletReturn;

use App\Actions\Fulfilment\PalletReturn\AutoAssignServicesToPalletReturn;
use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydratePallets;
use App\Actions\RetinaAction;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsCommand;

class RetinaAttachPalletsToReturn extends RetinaAction
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
            $palletsData = [];
            foreach ($palletsToSelect as $palletId) {
                $palletsData[$palletId] = ['quantity_ordered' => 1];
            }

            $palletReturn->pallets()->syncWithoutDetaching($palletsData);

            Pallet::whereIn('id', $palletsToSelect)->update([
                'pallet_return_id' => $palletReturn->id,
                'status'           => PalletStatusEnum::STORING,
                'state'            => PalletStateEnum::IN_PROCESS
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
        ]);

        $palletReturn->pallets()->detach($palletIds);

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
        return true;
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
