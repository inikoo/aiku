<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 00:49:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Models\HumanResources\Employee;
use Lorisleiva\Actions\ActionRequest;

class UpdateEmployee
{
    use WithActionUpdate;

    public function handle(Employee $employee, array $modelData): Employee
    {
        $employee =  $this->update($employee, $modelData, ['data', 'salary',]);

        EmployeeHydrateUniversalSearch::dispatch($employee);
        return $employee;
    }


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("hr.edit");
    }
    public function rules(): array
    {
        return [
            'name' => ['sometimes','required'],
        ];
    }


    public function asController(Employee $employee, ActionRequest $request): Employee
    {
        $request->validate();
        return $this->handle($employee, $request->all());
    }


    public function jsonResponse(Employee $employee): EmployeeResource
    {
        return new EmployeeResource($employee);
    }
}
