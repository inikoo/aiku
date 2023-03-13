<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 00:49:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Models\HumanResources\Employee;

class UpdateEmployee
{
    use WithActionUpdate;

    public function handle(Employee $employee, array $modelData): Employee
    {
        $employee =  $this->update($employee, $modelData, [
            'data',
            'salary',
        ]);
        EmployeeHydrateUniversalSearch::dispatch($employee);
        return $employee;
    }
}
