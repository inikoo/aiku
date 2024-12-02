<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 20-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Billables\Charge\Search;

use App\Actions\HydrateModel;
use App\Models\Billables\Charge;
use Illuminate\Support\Collection;

class ReindexChargeSearch extends HydrateModel
{
    public string $commandSignature = 'charges:search {organisations?*} {--s|slugs=} ';


    public function handle(Charge $charge): void
    {
        ChargeRecordSearch::run($charge);
    }

    protected function getModel(string $slug): Charge
    {
        return Charge::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Charge::withTrashed()->get();
    }
}
