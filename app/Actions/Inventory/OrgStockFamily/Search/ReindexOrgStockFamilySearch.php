<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 14-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Inventory\OrgStockFamily\Search;

use App\Actions\HydrateModel;
use App\Models\Inventory\OrgStockFamily;
use Illuminate\Support\Collection;

class ReindexOrgStockFamilySearch extends HydrateModel
{
    public string $commandSignature = 'search:org_stock_families {organisations?*} {--s|slugs=}';


    public function handle(OrgStockFamily $orgStockFamily): void
    {
        OrgStockFamilyRecordSearch::run($orgStockFamily);
    }


    protected function getModel(string $slug): OrgStockFamily
    {
        return OrgStockFamily::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return OrgStockFamily::withTrashed()->get();
    }
}
