<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 14-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Inventory\OrgStock\Search;

use App\Actions\HydrateModel;
use App\Models\Inventory\OrgStock;
use Illuminate\Support\Collection;

class ReindexOrgStockSearch extends HydrateModel
{
    public string $commandSignature = 'org_stock:search {organisations?*} {--s|slugs=}';


    public function handle(OrgStock $orgStock): void
    {
        OrgStockRecordSearch::run($orgStock);
    }


    protected function getModel(string $slug): OrgStock
    {
        return OrgStock::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return OrgStock::withTrashed()->get();
    }
}
