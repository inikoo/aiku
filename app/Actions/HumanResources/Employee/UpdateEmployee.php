<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 00:49:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\HumanResources\Employee\Search\EmployeeRecordSearch;
use App\Actions\HumanResources\JobPosition\SyncEmployeeJobPositions;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateEmployees;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateEmployees;
use App\Actions\SysAdmin\User\UpdateUser;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithPreparePositionsForValidation;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithReorganisePositions;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Enums\HumanResources\Employee\EmployeeTypeEnum;
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
    use WithPreparePositionsForValidation;
    use WithReorganisePositions;
    use WithNoStrictRules;

    protected bool $asAction = false;

    private Employee $employee;

    public function handle(Employee $employee, array $modelData): Employee
    {
        $positions = Arr::get($modelData, 'positions', []);
        data_forget($modelData, 'positions');
        $positions = $this->reorganisePositionsSlugsToIds($positions);

        SyncEmployeeJobPositions::run($employee, $positions);

        $credentials = Arr::only($modelData, ['username', 'password', 'auth_type', 'user_model_status']);

        data_forget($modelData, 'username');
        data_forget($modelData, 'password');
        data_forget($modelData, 'auth_type');
        data_forget($modelData, 'user_model_status');


        $employee = $this->update($employee, $modelData, ['data', 'salary']);

        if (Arr::hasAny($employee->getChanges(), ['worker_number', 'worker_number', 'contact_name', 'work_email', 'job_title', 'email'])) {
            EmployeeRecordSearch::dispatch($employee);
        }

        if (Arr::hasAny($employee->getChanges(), ['state'])) {
            GroupHydrateEmployees::dispatch($employee->group)->delay($this->hydratorsDelay);
            OrganisationHydrateEmployees::dispatch($employee->organisation)->delay($this->hydratorsDelay);
        }

        if ($user = $employee->getUser()) {
            if (Arr::exists($credentials, 'user_model_status')) {
                $employee->users()->updateExistingPivot($user->id, ['status' => $credentials['user_model_status']]);
                data_forget($credentials, 'user_model_status');
            }

            UpdateUser::run($user, $credentials);
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
            'state.employment_start_at'             => ['sometimes', 'nullable', 'date'],
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
                $this->strict ? 'max:24' : 'max:255',
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
            'type'                                  => ['sometimes', Rule::enum(EmployeeTypeEnum::class)]


        ];

        if (!$this->strict) {
            $rules = $this->noStrictUpdateRules($rules);
        }

        if ($user = $this->employee->getUser()) {
            $rules['username']          = [
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
                            'value'    => $user->id
                        ],
                    ]
                ),


            ];
            $rules['password']          = ['sometimes', 'required', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()];
            $rules['user_model_status'] = ['sometimes', 'boolean'];
        }


        return $rules;
    }

    public function action(Employee $employee, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): Employee
    {
        $this->strict = $strict;
        if (!$audit) {
            Employee::disableAuditing();
        }
        $this->asAction       = true;
        $this->employee       = $employee;
        $this->hydratorsDelay = $hydratorsDelay;
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
