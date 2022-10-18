<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:54:17 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\CustomerClient;

use App\Actions\Helpers\Address\StoreAddress;
use App\Models\Sales\Customer;
use App\Models\Sales\CustomerClient;
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
