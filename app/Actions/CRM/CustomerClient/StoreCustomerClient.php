<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 29 Jan 2022 00:55:05 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\CRM\CustomerClient;

use App\Actions\Helpers\Address\StoreAddress;
use App\Models\CRM\Customer;
use App\Models\CRM\CustomerClient;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreCustomerClient
{
    use AsAction;

    public function handle(Customer $customer, array $modelData, array $addressesData = []): CustomerClient
    {
        $modelData['shop_id'] = $customer->shop_id;

        /** @var CustomerClient $customerClient */
        $customerClient = $customer->clients()->create($modelData);


        $address = StoreAddress::run($addressesData);


        $customerClient->addresses()->sync([
                                               $address->id => [
                                                   'scope' => 'delivery'
                                               ]
                                           ]);
        $customerClient->delivery_address_id = $address->id;
        $customerClient->save();


        return $customerClient;
    }
}
