<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer;

use App\Actions\Helpers\Address\Hydrators\AddressHydrateUsage;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Helpers\Address;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class DeleteCustomerDeliveryAddress extends OrgAction
{

    protected Address $address;
    public function handle(Customer $customer, Address $address)
    {
        $customer->addresses()->detach($address->id);
        $customer->delivery_address_id = $customer->address_id;
        $customer->save();

        $address->delete();
        
        AddressHydrateUsage::dispatch($address);
        return $customer;
    }

    public function afterValidator(Validator $validator): void
    {

        if(DB::table('model_has_addresses')->where('address_id', $this->address->id)->exists()){
            abort(419);
        }

    }

    public function asController(FulfilmentCustomer $fulfilmentCustomer, Address $address, ActionRequest $request): void
    {
        $this->address = $address;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);
        $this->handle($fulfilmentCustomer->customer, $address);
    }
}
