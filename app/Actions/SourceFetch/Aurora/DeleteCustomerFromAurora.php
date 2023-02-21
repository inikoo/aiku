<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Feb 2023 18:25:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;


use App\Actions\Sales\Customer\DeleteCustomer;
use App\Models\Sales\Customer;
use App\Services\Tenant\SourceTenantService;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCustomerFromAurora
{

    use AsAction;

    public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Customer
    {
        if ($customer = Customer::withTrashed()->where('source_id', $tenantSourceId)->first()) {
            if (!$customer->trashed()) {
                DeleteCustomer::run(
                    customer: $customer
                );
            }
        } else {
            return FetchDeletedCustomers::run($tenantSource, $tenantSourceId);
        }

        return null;
    }


}
