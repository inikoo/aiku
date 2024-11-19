<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 19-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Procurement\OrgSupplier\Search;

use App\Actions\HydrateModel;
use App\Models\Procurement\OrgSupplier;
use Illuminate\Support\Collection;

class ReindexOrgSupplierSearch extends HydrateModel
{
    public string $commandSignature = 'org_supplier:search {organisations?*} {--s|slugs=} ';


    public function handle(OrgSupplier $orgSupplier): void
    {
        OrgSupplierRecordSearch::run($orgSupplier);
    }

    protected function getModel(string $slug): OrgSupplier
    {
        return OrgSupplier::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return OrgSupplier::all();
    }
}
