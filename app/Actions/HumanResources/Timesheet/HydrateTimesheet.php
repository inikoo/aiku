<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 01 May 2024 14:33:33 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Timesheet;

use App\Actions\HumanResources\Timesheet\Hydrators\TimesheetHydrateTimeTrackers;
use App\Actions\HydrateModel;
use App\Models\HumanResources\Timesheet;
use Illuminate\Support\Collection;

class HydrateTimesheet extends HydrateModel
{
    public string $commandSignature = 'timesheet:hydrate {organisations?*} {--s|slugs=}';


    public function handle(Timesheet $timesheet): void
    {
        TimesheetHydrateTimeTrackers::run($timesheet);
    }


    protected function getModel(string $slug): Timesheet
    {
        return Timesheet::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Timesheet::get();
    }
}
