<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 00:49:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateUniversalSearch;
use App\Actions\HumanResources\SyncJobPosition;
use App\Actions\InertiaOrganisationAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateEmployees;
use App\Actions\SysAdmin\User\UpdateUser;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Organisation;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class UpdateEmployee extends InertiaOrganisationAction
{
    use WithActionUpdate;


    protected bool $asAction = false;

    private Employee $employee;

    public function handle(Employee $employee, array $modelData): Employee
    {
        $credentials = [];
        if (Arr::exists($modelData, 'username')) {
            $credentials['username'] = Arr::get($modelData, 'username');
        }
        if (Arr::exists($modelData, 'password')) {
            $credentials['password'] = Arr::get($modelData, 'password');
        }

        Arr::forget($modelData, ['username', 'password']);


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

        if (count($credentials) > 0 and $employee->user) {
            UpdateUser::run($employee->user, $credentials);
        }


        return $employee;
    }


    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->slug}.edit");
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
            'state'               => ['required', new Enum(EmployeeStateEnum::class)],
            'positions'           => ['sometimes', 'array'],
            'positions.*'         => ['sometimes', 'exists:job_positions,slug'],
            'email'               => ['sometimes', 'nullable', 'email'],
            'username'            => ['nullable', new AlphaDashDot(), 'iunique:organisation_users'],
            'password'            => ['exclude_if:username,null', 'required', 'max:255', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
            'reset_password'      => ['sometimes', 'boolean'],
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

    public function asController(Organisation $organisation, Employee $employee, ActionRequest $request): Employee
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
