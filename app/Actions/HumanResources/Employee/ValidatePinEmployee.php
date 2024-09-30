<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 00:49:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\HumanResources\Employee\Traits\HasEmployeePositionGenerator;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\HumanResources\EmployeeHanResource;
use App\Models\HumanResources\Employee;
use Lorisleiva\Actions\ActionRequest;

class ValidatePinEmployee extends OrgAction
{
    use WithActionUpdate;
    use HasPositionsRules;
    use HasEmployeePositionGenerator;

    protected bool $asAction = false;

    private Employee $employee;

    public function handle(Employee $employee): Employee
    {
        return $employee;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }

    public function action(Employee $employee, array $modelData, bool $audit = true): Employee
    {
        if (!$audit) {
            Employee::disableAuditing();
        }
        $this->asAction = true;
        $this->employee = $employee;

        $this->initialisation($employee->organisation, $modelData);

        return $this->handle($employee);
    }

    public function asController(Employee $employee, ActionRequest $request): Employee
    {
        $this->employee = $employee;
        $this->initialisation($employee->organisation, $request);

        return $this->handle($employee);
    }

    public function jsonResponse(Employee $employee): EmployeeHanResource
    {
        return new EmployeeHanResource($employee);
    }
}
