<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 19-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Procurement\OrgPartner\Search;

use App\Actions\HydrateModel;
use App\Models\Procurement\OrgPartner;
use Illuminate\Support\Collection;

class ReindexOrgPartnerSearch extends HydrateModel
{
    public string $commandSignature = 'org_patner:search {organisations?*} {--s|slugs=} ';


    public function handle(OrgPartner $orgPartner): void
    {
        OrgPartnerRecordSearch::run($orgPartner);
    }

    protected function getModel(string $slug): OrgPartner
    {
        return OrgPartner::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return OrgPartner::all();
    }
}
