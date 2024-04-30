<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:36:28 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateClockings;
use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateJobPositionsShare;
use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateTimesheets;
use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateTimeTracker;
use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateWeekWorkingHours;
use App\Actions\HydrateModel;
use App\Models\HumanResources\Employee;
use Illuminate\Support\Collection;

class HydrateEmployee extends HydrateModel
{
    public string $commandSignature = 'employee:hydrate {organisations?*} {--s|slugs=}';


    public function handle(Employee $employee): void
    {
        EmployeeHydrateJobPositionsShare::run($employee);
        EmployeeHydrateWeekWorkingHours::run($employee);
        EmployeeHydrateTimesheets::run($employee);
        EmployeeHydrateClockings::run($employee);
        EmployeeHydrateTimeTracker::run($employee);
    }


    protected function getModel(string $slug): Employee
    {
        return Employee::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Employee::get();
    }
}
