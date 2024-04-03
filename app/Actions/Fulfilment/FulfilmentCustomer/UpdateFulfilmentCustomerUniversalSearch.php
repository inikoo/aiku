<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 14:34:58 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateUniversalSearch;
use App\Actions\HydrateModel;
use App\Models\Fulfilment\FulfilmentCustomer;
use Illuminate\Support\Collection;

class UpdateFulfilmentCustomerUniversalSearch extends HydrateModel
{
    public string $commandSignature = 'fulfilment-customer:search {organisations?*} {--s|slugs=}';


    public function handle(FulfilmentCustomer $fulfilmentCustomer): void
    {
        FulfilmentCustomerHydrateUniversalSearch::run($fulfilmentCustomer);
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
