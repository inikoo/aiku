<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 13-02-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Retina\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\PalletReturn\DeletePalletReturnAddress;
use App\Actions\RetinaAction;
use App\Models\Fulfilment\PalletReturn;
use Lorisleiva\Actions\ActionRequest;

class DeleteRetinaPalletReturnAddress extends RetinaAction
{
    public function handle(PalletReturn $palletReturn): PalletReturn
    {
        return DeletePalletReturnAddress::run($palletReturn);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($this->fulfilmentCustomer->id == $request->route()->parameter('palletReturn')->fulfilmentCustomer->id) {
            return true;
        }

        return false;
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): void
    {
        $this->initialisation($request);
        $this->handle($palletReturn);
    }

    public function action(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        $this->asAction = true;
        $this->initialisationFulfilmentActions($palletReturn->fulfilmentCustomer, $modelData);

        return $this->handle($palletReturn);
    }
}
