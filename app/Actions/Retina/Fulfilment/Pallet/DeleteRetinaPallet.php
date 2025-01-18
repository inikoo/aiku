<?php

/*
 * author Arya Permana - Kirin
 * created on 17-01-2025-09h-10m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\Fulfilment\Pallet;

use App\Actions\Fulfilment\Pallet\Search\PalletRecordSearch;
use App\Actions\Fulfilment\PalletDelivery\AutoAssignServicesToPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\Hydrators\PalletDeliveryHydratePallets;
use App\Actions\Fulfilment\PalletDelivery\Hydrators\PalletDeliveryHydrateTransactions;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use Lorisleiva\Actions\ActionRequest;

class DeleteRetinaPallet extends RetinaAction
{
    use WithActionUpdate;

    public function handle(Pallet $pallet): Pallet
    {
        $this->update($pallet, ['customer_reference' => null]);
        $pallet->delete();

        PalletDeliveryHydratePallets::run($pallet->palletDelivery);
        AutoAssignServicesToPalletDelivery::run($pallet->palletDelivery, $pallet);
        PalletDeliveryHydrateTransactions::run($pallet->palletDelivery);
        PalletRecordSearch::dispatch($pallet);
        return $pallet;
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

        return false;
    }

    public function asController(Pallet $pallet, ActionRequest $request): Pallet
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment   = $fulfilmentCustomer->fulfilment;

        $this->initialisation($request);
        return $this->handle($pallet);
    }

    public function jsonResponse(Pallet $pallet): PalletResource
    {
        return new PalletResource($pallet);
    }
}
