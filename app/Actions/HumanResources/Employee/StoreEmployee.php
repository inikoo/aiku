<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 22:01:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\Central\Tenant\Hydrators\TenantHydrateEmployees;
use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateUniversalSearch;
use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateWeekWorkingHours;
use App\Models\HumanResources\Employee;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreEmployee
{
    use AsAction;

    public function handle(array $modelData): Employee
    {
        $employee =  Employee::create($modelData);
        EmployeeHydrateWeekWorkingHours::run($employee);
        TenantHydrateEmployees::dispatch(app('currentTenant'));
        EmployeeHydrateUniversalSearch::run($employee);
        return $employee;
    }
}
