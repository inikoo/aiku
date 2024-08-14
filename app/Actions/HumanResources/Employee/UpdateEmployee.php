<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 00:49:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\HumanResources\Employee\Search\EmployeeRecordSearch;
use App\Actions\HumanResources\Employee\Traits\HasEmployeePositionGenerator;
use App\Actions\HumanResources\JobPosition\SyncEmployeeJobPositions;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateEmployees;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateEmployees;
use App\Actions\SysAdmin\User\UpdateUser;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Enums\SysAdmin\User\UserAuthTypeEnum;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Models\HumanResources\Employee;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use App\Rules\PinRule;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class UpdateEmployee extends OrgAction
{
    use WithActionUpdate;
    use HasPositionsRules;
    use HasEmployeePositionGenerator;

    protected bool $asAction = false;

    private Employee $employee;

    public function handle(Employee $employee, array $modelData): Employee
    {
        if (Arr::exists($modelData, 'positions')) {
            $jobPositions = $this->generatePositions($employee->organisation, $modelData);
            SyncEmployeeJobPositions::run($employee, $jobPositions);
            Arr::forget($modelData, 'positions');
        }


        $credentials = Arr::only($modelData, ['username', 'password', 'auth_type']);

        data_forget($modelData, 'username');
        data_forget($modelData, 'password');
        data_forget($modelData, 'auth_type');

        $employee = $this->update($employee, $modelData, ['data', 'salary']);

        if (Arr::hasAny($employee->getChanges(), ['worker_number', 'worker_number', 'contact_name', 'work_email', 'job_title', 'email'])) {
            EmployeeRecordSearch::dispatch($employee);
        }

        if (Arr::hasAny($employee->getChanges(), ['state'])) {
            GroupHydrateEmployees::dispatch($employee->group);
            OrganisationHydrateEmployees::dispatch($employee->organisation);
        }

        if ($employee->user) {
            UpdateUser::run($employee->user, $credentials);
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
        $rules = [
            'worker_number'                         => [
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
            'employment_start_at'                   => ['sometimes', 'nullable', 'date'],
            'work_email'                            => [
                'sometimes',
                'nullable',
                'email',
                new IUnique(
                    table: 'employees',
                    extraConditions: [

                        [
                            'column'   => 'group_id',
                            'operator' => '=',
                            'value'    => $this->employee->group_id
                        ],
                    ]
                ),
            ],
            'alias'                                 => [
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
            'pin'                                   => ['sometimes', new PinRule($this->employee->organisation_id)],
            'contact_name'                          => ['sometimes', 'string', 'max:256'],
            'date_of_birth'                         => ['sometimes', 'nullable', 'date', 'before_or_equal:today'],
            'job_title'                             => ['sometimes', 'nullable', 'string', 'max:256'],
            'state'                                 => ['sometimes', 'required', new Enum(EmployeeStateEnum::class)],
            'positions'                             => ['sometimes', 'array'],
            'positions.*.slug'                      => ['sometimes', 'string'],
            'positions.*.scopes'                    => ['sometimes', 'array'],
            'positions.*.scopes.warehouses.slug.*'  => ['sometimes', Rule::exists('warehouses', 'slug')->where('organisation_id', $this->organisation->id)],
            'positions.*.scopes.fulfilments.slug.*' => ['sometimes', Rule::exists('fulfilments', 'slug')->where('organisation_id', $this->organisation->id)],
            'positions.*.scopes.shops.slug.*'       => ['sometimes', Rule::exists('shops', 'slug')->where('organisation_id', $this->organisation->id)],
            'email'                                 => ['sometimes', 'nullable', 'email'],
            'source_id'                             => ['sometimes', 'string', 'max:64'],
        ];

        if ($this->employee->user) {
            $rules['username']  = [
                'sometimes',
                'required',
                'lowercase',
                new AlphaDashDot(),

                Rule::notIn(['export', 'create']),
                new IUnique(
                    table: 'users',
                    extraConditions: [

                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->employee->user->id
                        ],
                    ]
                ),


            ];
            $rules['password']  = ['sometimes', 'required', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()];

        }


        return $rules;
    }

    public function action(Employee $employee, $modelData): Employee
    {
        $this->asAction = true;
        $this->employee = $employee;

        $this->initialisation($employee->organisation, $modelData);

        return $this->handle($employee, $this->validatedData);
    }


    public function prepareForValidation(ActionRequest $request): void
    {
        $this->preparePositionsForValidation();
        if ($this->has('password')) {
            {
                $this->set('auth_type', UserAuthTypeEnum::DEFAULT);
            }
        }
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
