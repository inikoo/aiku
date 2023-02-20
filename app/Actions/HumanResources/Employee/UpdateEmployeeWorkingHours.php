<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 11:43:46 Malaysia Time, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee;


use App\Models\HumanResources\Employee;
use Lorisleiva\Actions\Concerns\AsAction;


class UpdateEmployeeWorkingHours
{
    use AsAction;

    public function handle(Employee $employee, array $workingHours): Employee
    {
        $employee->update(
            [
                'working_hours' => $workingHours
            ]
        );
        HydrateEmployee::make()->weekWorkingHours($employee);

        return $employee;
    }


}
