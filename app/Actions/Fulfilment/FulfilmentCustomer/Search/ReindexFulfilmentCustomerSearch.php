<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:43:03 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer\Search;

use App\Actions\HydrateModel;
use App\Models\Fulfilment\FulfilmentCustomer;
use Illuminate\Support\Collection;

class ReindexFulfilmentCustomerSearch extends HydrateModel
{
    public string $commandSignature = 'fulfilment-customer:search {organisations?*} {--s|slugs=}';


    public function handle(FulfilmentCustomer $fulfilmentCustomer): void
    {
        FulfilmentCustomerRecordSearch::run($fulfilmentCustomer);
    }


    protected function getModel(string $slug): FulfilmentCustomer
    {
        return FulfilmentCustomer::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return FulfilmentCustomer::get();
    }
}
