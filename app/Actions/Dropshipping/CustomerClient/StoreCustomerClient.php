<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 30 Oct 2022 01:03:02 Greenwich Mean Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\CustomerClient;

use App\Actions\Dropshipping\CustomerClient\Hydrators\CustomerClientHydrateUniversalSearch;
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Sales\Customer\Hydrators\CustomerHydrateClients;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Sales\Customer;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreCustomerClient
{
    use AsAction;

    public function handle(Customer $customer, array $modelData, array $addressesData = []): CustomerClient
    {
        $modelData['shop_id'] = $customer->shop_id;

        /** @var CustomerClient $customerClient */
        $customerClient = $customer->clients()->create($modelData);


        StoreAddressAttachToModel::run($customerClient, $addressesData, ['scope' => 'delivery']);
        CustomerClientHydrateUniversalSearch::dispatch($customerClient);
        CustomerHydrateClients::dispatch($customer);

        return $customerClient;
    }
}
