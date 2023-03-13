<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:54:17 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer;

use App\Actions\Central\Tenant\Hydrators\TenantHydrateCustomers;
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateCustomerInvoices;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateCustomers;
use App\Actions\Sales\Customer\Hydrators\CustomerHydrateUniversalSearch;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use Illuminate\Support\Facades\Bus;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreCustomer
{
    use AsAction;

    public function handle(Shop $shop, array $customerData, array $customerAddressesData = []): Customer
    {
        /** @var \App\Models\Sales\Customer $customer */
        $customer = $shop->customers()->create($customerData);
        $customer->stats()->create();

        StoreAddressAttachToModel::run($customer, $customerAddressesData, ['scope' => 'contact']);
        $customer->location = $customer->getLocation();
        $customer->save();


        Bus::chain([
            ShopHydrateCustomers::makeJob($customer->shop),
            ShopHydrateCustomerInvoices::makeJob($customer->shop)
        ])->dispatch();



        TenantHydrateCustomers::dispatch(app('currentTenant'));

        CustomerHydrateUniversalSearch::dispatch($customer);

        return $customer;
    }
}
