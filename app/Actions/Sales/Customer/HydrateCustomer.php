<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 03 Feb 2022 01:40:54 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Sales\Customer;

use App\Actions\HydrateModel;
use App\Actions\Sales\Customer\Hydrators\CustomerHydrateClients;
use App\Actions\Sales\Customer\Hydrators\CustomerHydrateInvoices;
use App\Actions\Sales\Customer\Hydrators\CustomerHydrateUniversalSearch;
use App\Actions\Sales\Customer\Hydrators\CustomerHydrateWebUsers;
use App\Models\Sales\Customer;
use Illuminate\Support\Collection;

class HydrateCustomer extends HydrateModel
{
    public string $commandSignature = 'hydrate:customer {tenants?*} {--i|id=}';


    public function handle(Customer $customer): void
    {
        CustomerHydrateInvoices::run($customer);
        CustomerHydrateWebUsers::run($customer);
        CustomerHydrateClients::run($customer);
        CustomerHydrateUniversalSearch::run($customer);
    }

    protected function getModel(int $id): Customer
    {
        return Customer::find($id);
    }

    protected function getAllModels(): Collection
    {
        return Customer::withTrashed()->get();
    }
}
