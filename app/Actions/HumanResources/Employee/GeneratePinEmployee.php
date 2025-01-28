<?php

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 00:49:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\HumanResources\Employee;
use Lorisleiva\Actions\ActionRequest;

class GeneratePinEmployee extends OrgAction
{
    use WithActionUpdate;

    public function handle(Employee $employee): array
    {
        $employeePin = SetEmployeePin::make()->action($employee, false, true);

        return [
            'pin' => $employeePin
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }

    public function asController(Employee $employee, ActionRequest $request): array
    {
        $this->initialisation($employee->organisation, $request);

        return $this->handle($employee);
    }
}
