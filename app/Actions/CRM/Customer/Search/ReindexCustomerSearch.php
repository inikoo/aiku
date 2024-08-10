<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Aug 2024 22:13:51 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\Search;

use App\Actions\HydrateModel;
use App\Models\CRM\Customer;
use Illuminate\Support\Collection;

class ReindexCustomerSearch extends HydrateModel
{
    public string $commandSignature = 'customer:search {organisations?*} {--s|slugs=}';


    public function handle(Customer $customer): void
    {
        CustomerRecordSearch::run($customer);
    }


    protected function getModel(string $slug): Customer
    {
        return Customer::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Customer::withTrashed()->get();
    }
}
