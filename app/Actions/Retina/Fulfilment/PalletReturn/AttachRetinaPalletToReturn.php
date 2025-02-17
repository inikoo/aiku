<?php
/*
 * author Arya Permana - Kirin
 * created on 17-02-2025-14h-08m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\Pallet\AttachPalletToReturn;
use App\Actions\Fulfilment\Pallet\DeletePallet;
use App\Actions\RetinaAction;
use App\Http\Resources\Fulfilment\RetinaPalletResource;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletReturn;
use Lorisleiva\Actions\ActionRequest;

class AttachRetinaPalletToReturn extends RetinaAction
{
    public function handle(PalletReturn $palletReturn, Pallet $pallet): PalletReturn
    {
        return AttachPalletToReturn::run($palletReturn, $pallet);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($this->fulfilmentCustomer->id == $request->route()->parameter('palletReturn')->fulfilment_customer_id
            and $this->fulfilmentCustomer->id == $request->route()->parameter('pallet')->fulfilment_customer_id
        ) {
            return true;
        }

        return false;
    }

    public function asController(PalletReturn $palletReturn, Pallet $pallet, ActionRequest $request): PalletReturn
    {
        $this->initialisation($request);
        
        return $this->handle($palletReturn, $pallet);
    }

}