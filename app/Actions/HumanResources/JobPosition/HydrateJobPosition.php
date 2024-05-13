<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:36:28 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition;

use App\Actions\HumanResources\JobPosition\Hydrators\JobPositionHydrateEmployees;
use App\Actions\HumanResources\JobPosition\Hydrators\JobPositionHydrateGuests;
use App\Actions\HydrateModel;
use App\Models\HumanResources\JobPosition;
use Illuminate\Support\Collection;

class HydrateJobPosition extends HydrateModel
{
    public string $commandSignature = 'job-position:hydrate {organisations?*} {--s|slugs=}';


    public function handle(JobPosition $jobPosition): void
    {
        JobPositionHydrateEmployees::run($jobPosition);
        JobPositionHydrateGuests::run($jobPosition);
    }


    protected function getModel(string $slug): JobPosition
    {
        return JobPosition::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return JobPosition::get();
    }
}
