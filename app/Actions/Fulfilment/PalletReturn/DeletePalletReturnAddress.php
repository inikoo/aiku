<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 21-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Helpers\Address\Hydrators\AddressHydrateUsage;
use App\Actions\OrgAction;
use App\Models\Fulfilment\PalletReturn;
use Lorisleiva\Actions\ActionRequest;

class DeletePalletReturnAddress extends OrgAction
{
    public function handle(PalletReturn $palletreturn): PalletReturn
    {
        $addressDelivery = $palletreturn->deliveryAddress;
        $palletreturn->addresses()->detach($addressDelivery->id);
        AddressHydrateUsage::dispatch($palletreturn->deliveryAddress);
        $addressDelivery->delete();
        $palletreturn->refresh();
        return $palletreturn;
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): void
    {
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $request);
        $this->handle($palletReturn);
    }

    public function action(PalletReturn $palletreturn): PalletReturn
    {
        $this->initialisationFromShop($palletreturn->shop, []);
        return $this->handle($palletreturn);
    }
}
