<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateClients;
use App\Actions\CRM\Customer\Hydrators\CustomerHydrateInvoices;
use App\Actions\CRM\Customer\Hydrators\CustomerHydrateUniversalSearch;
use App\Actions\CRM\Customer\Hydrators\CustomerHydrateWebUsers;
use App\Actions\HydrateModel;
use App\Models\CRM\Customer;
use Illuminate\Support\Collection;

class HydrateCustomer extends HydrateModel
{
    public string $commandSignature = 'hydrate:customer {organisations?*} {--i|id=}';


    public function handle(Customer $customer): void
    {
        CustomerHydrateInvoices::run($customer);
        CustomerHydrateWebUsers::run($customer);
        CustomerHydrateClients::run($customer);
        CustomerHydrateUniversalSearch::run($customer);
    }

    protected function getModel(string $slug): Customer
    {
        return Customer::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Customer::withTrashed()->get();
    }
}
