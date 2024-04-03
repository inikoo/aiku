<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 14:34:58 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateUniversalSearch;
use App\Actions\HydrateModel;
use App\Models\CRM\Customer;
use Illuminate\Support\Collection;

class UpdateCustomerUniversalSearch extends HydrateModel
{
    public string $commandSignature = 'customer:search {organisations?*} {--s|slugs=}';


    public function handle(Customer $customer): void
    {
        CustomerHydrateUniversalSearch::run($customer);
    }


    protected function getModel(string $slug): Customer
    {
        return Customer::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Customer::get();
    }
}
