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
use App\Models\Helpers\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class DeletePalletReturnAddress extends OrgAction
{
    protected Address $address;
    public function handle(PalletReturn $palletreturn, Address $address): PalletReturn
    {
        $palletreturn->addresses()->detach($address->id);

        AddressHydrateUsage::dispatch($address);

        $address->delete();

        $palletreturn->refresh();
        return $palletreturn;
    }

    public function afterValidator(Validator $validator): void
    {

        if (DB::table('model_has_addresses')->where('address_id', $this->address->id)->where('model_type', '!=', 'PalletReturn')->exists()) {
            abort(419);
        }

    }

    public function asController(PalletReturn $palletReturn, Address $address, ActionRequest $request): void
    {
        $this->address = $address;
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $request);
        $this->handle($palletReturn, $address);
    }

    public function action(PalletReturn $palletreturn, Address $address): PalletReturn
    {
        $this->address = $address;
        $this->initialisationFromShop($palletreturn->shop, []);
        return $this->handle($palletreturn, $address);
    }
}
