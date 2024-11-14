<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 14-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\HumanResources\JobPosition\Search;

use App\Actions\HydrateModel;
use App\Models\HumanResources\JobPosition;
use Illuminate\Support\Collection;

class ReindexJobPositionSearch extends HydrateModel
{
    public string $commandSignature = 'jobPosition:search {organisations?*} {--s|slugs=}';


    public function handle(JobPosition $jobPosition): void
    {
        JobPositionRecordSearch::run($jobPosition);
    }


    protected function getModel(string $slug): JobPosition
    {
        return JobPosition::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return JobPosition::all();
    }
}
