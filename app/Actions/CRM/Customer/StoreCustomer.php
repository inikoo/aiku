<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 13 Oct 2021 20:00:51 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\CRM\Customer;

use App\Actions\Helpers\Address\StoreAddress;
use App\Models\Utils\ActionResult;
use App\Models\CRM\Customer;
use App\Models\Marketing\Shop;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreCustomer
{
    use AsAction;

    public function handle(
        Shop $shop,
        array $customerData,
        array $customerAddressesData = []
    ): ActionResult {
        $res = new ActionResult();

        $customerData['organisation_id']=$shop->organisation_id;

        /** @var Customer $customer */
        $customer = $shop->customers()->create($customerData);
        $customer->stats()->create(['organisation_id'=>$customer->organisation_id]);
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

        $res->model    = $customer;
        $res->model_id = $customer->id;
        $res->status   = $res->model_id ? 'inserted' : 'error';

        return $res;
    }
}
