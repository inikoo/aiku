<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:32:43 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


/** @noinspection PhpUnused */


namespace App\Actions\SourceFetch\Aurora;

use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\CRM\Customer\UpdateCustomer;
use App\Models\CRM\Customer;
use App\Services\Tenant\SourceTenantService;
use JetBrains\PhpStorm\NoReturn;



class FetchCustomers extends FetchAction
{

    public string $commandSignature = 'fetch:customers {tenants?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Customer
    {
        if ($customerData = $tenantSource->fetchCustomer($tenantSourceId)) {
            if ($customer = Customer::where('source_id', $customerData['customer']['source_id'])
                ->first()) {
                $customer = UpdateCustomer::run($customer, $customerData['customer']);
            } else {
                $customer = StoreCustomer::run($customerData['shop'], $customerData['customer'], $customerData['addresses']);
            }

            return $customer;
        }

        return null;
    }


}
