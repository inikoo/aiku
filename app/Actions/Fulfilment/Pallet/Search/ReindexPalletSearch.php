<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:39:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\Search;

use App\Actions\HydrateModel;
use App\Models\Accounting\Invoice;
use App\Models\Fulfilment\Pallet;
use Illuminate\Support\Collection;

class ReindexPalletSearch extends HydrateModel
{
    public string $commandSignature = 'pallet:search {organisations?*} {--s|slugs=}';


    public function handle(Pallet $pallet): void
    {
        PalletRecordSearch::run($pallet);
    }


    protected function getModel(string $slug): Invoice
    {
        return Pallet::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Pallet::withTrashed()->get();
    }
}
