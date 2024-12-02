<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 14 Jun 2024 19:14:53 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydratePallets;
use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydrateStoredItems;
use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydrateTransactions;
use App\Actions\HydrateModel;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Support\Collection;

class HydratePalletReturn extends HydrateModel
{
    public string $commandSignature = 'hydrate:pallet-returns {organisations?*} {--s|slugs=}';


    public function handle(PalletReturn $palletReturn): void
    {
        PalletReturnHydratePallets::run($palletReturn);
        PalletReturnHydrateStoredItems::run($palletReturn);
        PalletReturnHydrateTransactions::run($palletReturn);
    }

    protected function getModel(string $slug): PalletReturn
    {
        return PalletReturn::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return PalletReturn::withTrashed()->get();
    }
}
