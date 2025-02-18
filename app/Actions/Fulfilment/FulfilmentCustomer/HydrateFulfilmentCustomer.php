<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 19:28:01 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydratePalletDeliveries;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydratePalletReturns;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydratePallets;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateRecurringBills;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateSpaces;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateStatus;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateStoredItems;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateStoredItemAudits;
use App\Actions\HydrateModel;
use App\Models\Fulfilment\FulfilmentCustomer;
use Illuminate\Support\Collection;

class HydrateFulfilmentCustomer extends HydrateModel
{
    public string $commandSignature = 'hydrate:fulfilment_customers {organisations?*} {--s|slugs=}';


    public function handle(FulfilmentCustomer $fulfilmentCustomer): void
    {
        FulfilmentCustomerHydratePalletReturns::run($fulfilmentCustomer);
        FulfilmentCustomerHydratePalletDeliveries::run($fulfilmentCustomer);
        FulfilmentCustomerHydratePallets::run($fulfilmentCustomer);
        FulfilmentCustomerHydrateStoredItems::run($fulfilmentCustomer);
        FulfilmentCustomerHydrateStoredItemAudits::run($fulfilmentCustomer);
        FulfilmentCustomerHydrateRecurringBills::run($fulfilmentCustomer);
        FulfilmentCustomerHydrateStatus::run($fulfilmentCustomer);
        FulfilmentCustomerHydrateSpaces::run($fulfilmentCustomer);
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
