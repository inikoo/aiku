<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 19-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Procurement\OrgAgent\Search;

use App\Actions\HydrateModel;
use App\Models\Procurement\OrgAgent;
use Illuminate\Support\Collection;

class ReindexOrgAgentSearch extends HydrateModel
{
    public string $commandSignature = 'search:org_agents {organisations?*} {--s|slugs=} ';


    public function handle(OrgAgent $orgAgent): void
    {
        OrgAgentRecordSearch::run($orgAgent);
    }

    protected function getModel(string $slug): OrgAgent
    {
        return OrgAgent::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return OrgAgent::all();
    }
}
