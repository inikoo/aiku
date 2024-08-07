<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer;

use App\Actions\Helpers\Address\Hydrators\AddressHydrateUsage;
use App\Actions\OrgAction;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Helpers\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * Summary of DeleteCustomerDeliveryAddress
 * @author Kirin (Arya Permana)
 * @copyright (c) 2024
 */
class DeleteCustomerDeliveryAddress extends OrgAction
{
    protected Address $address;
    public function handle(Customer $customer, Address $address)
    {
        $customer->addresses()->detach($address->id);

        $address->delete();

        AddressHydrateUsage::dispatch($address);
        return $customer;
    }

    public function afterValidator(Validator $validator): void
    {

        if(DB::table('model_has_addresses')->where('address_id', $this->address->id)->where('model_type', '!=', 'Customer')->exists()) {
            abort(419);
        }

    }

    public function asController(FulfilmentCustomer $fulfilmentCustomer, Address $address, ActionRequest $request): void
    {
        $this->address = $address;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);
        $this->handle($fulfilmentCustomer->customer, $address);
    }

    public function inCustomer(Customer $customer, Address $address, ActionRequest $request): void
    {
        $this->address = $address;
        $this->initialisationFromShop($customer->shop, $request);
        $this->handle($customer, $address);
    }

    public function fromRetina(Customer $customer, Address $address, ActionRequest $request): Customer
    {
        $this->address = $address;
        $customer      = $request->user()->customer;

        $this->initialisation($request->get('website')->organisation, $request);

        return $this->handle($customer, $address);
    }
}
