<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\PalletReturn\CancelPalletReturn;
use App\Actions\RetinaAction;
use App\Http\Resources\Fulfilment\RetinaPalletReturnResource;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Http\Resources\Json\JsonResource;
use Lorisleiva\Actions\ActionRequest;

class CancelRetinaPalletReturn extends RetinaAction
{
    public function handle(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        return CancelPalletReturn::run($palletReturn, $modelData);
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

    public function jsonResponse(PalletReturn $palletReturn): JsonResource
    {
        return new RetinaPalletReturnResource($palletReturn);
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->initialisation($request);

        return $this->handle($palletReturn, $this->validatedData);
    }

    public function action(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        $this->asAction = true;
        $this->initialisationFulfilmentActions($palletReturn->fulfilmentCustomer, $modelData);

        return $this->handle($palletReturn, $this->validatedData);
    }
}
