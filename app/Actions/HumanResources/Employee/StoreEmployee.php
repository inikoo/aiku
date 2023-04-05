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
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreEmployee
{
    use AsAction;
    use WithAttributes;

    public function handle(array $modelData): Employee
    {
        $employee = Employee::create($modelData);
        EmployeeHydrateWeekWorkingHours::run($employee);
        TenantHydrateEmployees::dispatch(app('currentTenant'));
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
            'name'          => ['required'],
            'date_of_birth' => ['sometimes', 'date'],
            'email'         => ['sometimes', 'email'],

        ];
    }

    public function asController(ActionRequest $request): Employee
    {
        $request->validate();

        return $this->handle($request->validated());
    }
}
