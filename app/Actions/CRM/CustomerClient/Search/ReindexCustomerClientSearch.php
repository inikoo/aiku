<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Sept 2024 23:05:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\CustomerClient\Search;

use App\Actions\HydrateModel;
use App\Models\Dropshipping\CustomerClient;
use Illuminate\Support\Collection;

class ReindexCustomerClientSearch extends HydrateModel
{
    public string $commandSignature = 'customer-client:search {organisations?*} {--s|slugs=}';


    public function handle(CustomerClient $customerClient): void
    {
        CustomerClientRecordSearch::run($customerClient);
    }

    protected function getModel(string $slug): CustomerClient
    {
        return CustomerClient::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return CustomerClient::withTrashed()->get();
    }
}
