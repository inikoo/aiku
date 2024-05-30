<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 11:43:46 Malaysia Time, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateWeekWorkingHours;
use App\Http\Resources\SysAdmin\UsersResource;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateEmployeeWorkingHours
{
    use AsAction;
    use WithAttributes;

    public function handle(Employee $employee, array $workingHours): Employee
    {
        $employee->update(
            [
                'working_hours' => $workingHours
            ]
        );
        EmployeeHydrateWeekWorkingHours::run($employee);
        return $employee;
    }


    public function rules(): array
    {
        return [
            'working_hours' => ['required']
        ];
    }

    public function action(Employee $employee, array $workingHours): Employee
    {
        $this->setRawAttributes($workingHours);
        $validatedData = $this->validateAttributes();

        return $this->handle($employee, $validatedData);
    }

    public function jsonResponse(User $user): UsersResource
    {
        return new UsersResource($user);
    }
}
