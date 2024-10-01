<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 00:49:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\OrgAction;
use App\Actions\Traits\WithPreparePositionsForValidation;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\HumanResources\EmployeeHanResource;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Employee;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class ValidatePinEmployee extends OrgAction
{
    use WithActionUpdate;
    use WithPreparePositionsForValidation;

    protected bool $asAction = false;

    private Employee $employee;
    private ClockingMachine $clockingMachine;

    public function handle(ClockingMachine $clockingMachine, array $modalData): Employee
    {
        return $clockingMachine->workplace->employees()->where('pin', Arr::get($modalData, 'pin'))->first();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function action(ClockingMachine $clockingMachine, array $modelData, bool $audit = true): Employee
    {
        $this->clockingMachine = $clockingMachine;

        if (!$audit) {
            Employee::disableAuditing();
        }
        $this->asAction = true;

        $this->initialisation($clockingMachine->organisation, $modelData);

        return $this->handle($clockingMachine, $modelData);
    }

    public function rules(): array
    {
        $clockingMachineId = $this->clockingMachine->id;

        return [
            'pin' => ['required', Rule::exists('employees', 'pin')->where(function ($query) use ($clockingMachineId) {
                $query->whereHas('workplaces', function ($query) use ($clockingMachineId) {
                    $query->where('clocking_machine_id', $clockingMachineId);
                });
            })]
        ];
    }

    public function asController(ActionRequest $request): Employee
    {
        /** @var ClockingMachine $clockingMachine */
        $clockingMachine = $request->user();
        $this->clockingMachine = $clockingMachine;

        $this->initialisation($clockingMachine->organisation, $request);

        return $this->handle($clockingMachine, $this->validatedData);
    }

    public function jsonResponse(Employee $employee): EmployeeHanResource
    {
        return new EmployeeHanResource($employee);
    }
}
