<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 14-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\HumanResources\Workplace\Search;

use App\Actions\HydrateModel;
use App\Models\HumanResources\Workplace;
use Illuminate\Support\Collection;

class ReindexWorkplaceSearch extends HydrateModel
{
    public string $commandSignature = 'workplace:search {organisations?*} {--s|slugs=}';


    public function handle(Workplace $workplace): void
    {
        WorkplaceRecordSearch::run($workplace);
    }


    protected function getModel(string $slug): Workplace
    {
        return Workplace::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Workplace::withTrashed()->get();
    }
}
