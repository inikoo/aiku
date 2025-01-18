<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 07-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Retina\Fulfilment\Pallet\Search;

use App\Actions\HydrateModel;
use App\Models\Fulfilment\Pallet;
use Illuminate\Support\Collection;

class ReindexPalletSearch extends HydrateModel
{
    public string $commandSignature = 'search:retina_pallets {organisations?*} {--s|slugs=}';


    public function handle(Pallet $pallet): void
    {
        PalletRecordSearch::run($pallet);
    }


    protected function getModel(string $slug): Pallet
    {
        return Pallet::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Pallet::withTrashed()->get();
    }
}
