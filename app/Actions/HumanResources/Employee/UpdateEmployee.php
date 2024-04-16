<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 00:49:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateUniversalSearch;
use App\Actions\HumanResources\SyncJobPosition;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateEmployees;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Lorisleiva\Actions\ActionRequest;

class UpdateEmployee extends OrgAction
{
    use WithActionUpdate;


    protected bool $asAction = false;

    private Employee $employee;

    public function handle(Employee $employee, array $modelData): Employee
    {

        if (Arr::exists($modelData, 'positions')) {
            $jobPositions = [];
            foreach (Arr::get($modelData, 'positions', []) as $position) {
                $jobPosition    = JobPosition::firstWhere('slug', $position);
                $jobPositions[] = $jobPosition->id;
            }
            SyncJobPosition::run($employee, $jobPositions);
            Arr::forget($modelData, 'positions');
        }
        $employee = $this->update($employee, $modelData, ['data', 'salary']);



        if ($employee->wasChanged(['worker_number', 'worker_number', 'contact_name', 'work_email', 'job_title', 'email'])) {
            EmployeeHydrateUniversalSearch::dispatch($employee);
        }

        if ($employee->wasChanged(['state'])) {
            OrganisationHydrateEmployees::dispatch($employee->organisation);
        }



        return $employee;
    }


    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }

    public function rules(): array
    {
        return [
            'worker_number'       => [
                'sometimes',
                'max:64',
                'alpha_dash',
                new IUnique(
                    table: 'employees',
                    extraConditions: [
                        [
                            'column' => 'organisation_id',
                            'value'  => $this->organisation->id
                        ],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->employee->id
                        ],
                    ]
                ),

            ],
            'employment_start_at' => ['sometimes', 'nullable', 'date'],
            'work_email'          => ['sometimes', 'nullable', 'email', 'iunique:employees'],
            'alias'               => [
                'sometimes',
                'string',
                'max:16',
                new IUnique(
                    table: 'employees',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->organisation->id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->employee->id
                        ],
                    ]
                ),
            ],
            'contact_name'        => ['sometimes', 'string', 'max:256'],
            'date_of_birth'       => ['sometimes', 'nullable', 'date', 'before_or_equal:today'],
            'job_title'           => ['sometimes', 'nullable', 'string', 'max:256'],
            'state'               => ['sometimes','required', new Enum(EmployeeStateEnum::class)],
            'positions'           => ['sometimes', 'array'],
            'positions.*'         => ['sometimes',
                Rule::exists('job_positions', 'code')
                    ->where('organisation_id', $this->organisation->id),
            ],
            'email'               => ['sometimes', 'nullable', 'email'],
            'source_id'           => ['sometimes', 'string', 'max:64'],
        ];
    }

    public function action(Employee $employee, $modelData): Employee
    {
        $this->asAction = true;
        $this->employee = $employee;

        $this->initialisation($employee->organisation, $modelData);

        return $this->handle($employee, $this->validatedData);
    }

    public function asController(Employee $employee, ActionRequest $request): Employee
    {
        $this->employee = $employee;
        $this->initialisation($employee->organisation, $request);

        return $this->handle($employee, $this->validatedData);
    }

    public function jsonResponse(Employee $employee): EmployeeResource
    {
        return new EmployeeResource($employee);
    }
}
