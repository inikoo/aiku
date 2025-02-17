<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 23:14:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\PalletReturn\DetachPalletFromReturn;
use App\Actions\RetinaAction;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletReturn;
use Lorisleiva\Actions\ActionRequest;

class DetachRetinaPalletFromReturn extends RetinaAction
{
    public function handle(PalletReturn $palletReturn, Pallet $pallet): bool
    {
        return DetachPalletFromReturn::run($palletReturn, $pallet);
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

    public function asController(PalletReturn $palletReturn, Pallet $pallet, ActionRequest $request): bool
    {
        $this->initialisation($request);

        return $this->handle($palletReturn, $pallet);
    }

    public function action(PalletReturn $palletReturn, Pallet $pallet, array $modelData): bool
    {
        $this->asAction = true;
        $this->initialisationFulfilmentActions($palletReturn->fulfilmentCustomer, $modelData);

        return $this->handle($palletReturn, $pallet);
    }
}
