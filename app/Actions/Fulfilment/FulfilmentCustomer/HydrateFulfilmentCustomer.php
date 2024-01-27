<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 19:28:01 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydratePallets;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateStoredItems;
use App\Actions\HydrateModel;
use App\Models\Fulfilment\FulfilmentCustomer;
use Illuminate\Support\Collection;

class HydrateFulfilmentCustomer extends HydrateModel
{
    public string $commandSignature = 'customer-fulfilment:hydrate {organisations?*} {--s|slugs=}';


    public function handle(FulfilmentCustomer $fulfilmentCustomer): void
    {
        FulfilmentCustomerHydratePallets::run($fulfilmentCustomer);
        FulfilmentCustomerHydrateStoredItems::run($fulfilmentCustomer);
    }

    protected function getModel(string $slug): FulfilmentCustomer
    {
        return FulfilmentCustomer::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return FulfilmentCustomer::withTrashed()->get();
    }
}
