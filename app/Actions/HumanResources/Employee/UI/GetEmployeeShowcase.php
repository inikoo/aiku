<?php
/*
 * author Arya Permana - Kirin
 * created on 03-01-2025-10h-39m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\HumanResources\Employee\UI;

use App\Http\Resources\HumanResources\EmployeeResource;
use App\Models\HumanResources\Employee;
use Lorisleiva\Actions\Concerns\AsObject;

class GetEmployeeShowcase
{
    use AsObject;

    public function handle(Employee $employee): array
    {

        return [
            'employee' => EmployeeResource::make($employee),
            'pin'      => $employee->pin
        ];
    }
}
