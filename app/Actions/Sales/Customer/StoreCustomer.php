<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:54:17 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer;

use App\Actions\Helpers\Address\StoreAddress;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreCustomer
{
    use AsAction;

    public function handle(Shop $shop, array $customerData, array $customerAddressesData = []): Customer
    {

        /** @var \App\Models\Sales\Customer $customer */
        $customer = $shop->customers()->create($customerData);
        $customer->stats()->create();
        $addresses = [];

        $billing_address_id  = null;
        $delivery_address_id = null;


        foreach ($customerAddressesData as $scope => $addressesData) {
            foreach ($addressesData as $addressData) {
                $address                 = StoreAddress::run($addressData);
                $addresses[$address->id] = ['scope' => $scope];
                if ($scope == 'billing') {
                    $billing_address_id = $address->id;
                } elseif ($scope == 'delivery') {
                    $delivery_address_id = $address->id;
                }
            }
        }

        if (!$delivery_address_id and $shop->type == 'shop') {
            $delivery_address_id = $billing_address_id;
        }


        $customer->addresses()->sync($addresses);
        $customer->billing_address_id  = $billing_address_id;
        $customer->delivery_address_id = $delivery_address_id;

        if($customer->billingAddress){
            $customer->location=$customer->billingAddress->getLocation();

        }

        $customer->save();
        return $customer;
    }
}
