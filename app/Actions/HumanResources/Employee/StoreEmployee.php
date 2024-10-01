<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:34:12 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateWeekWorkingHours;
use App\Actions\HumanResources\Employee\Search\EmployeeRecordSearch;
use App\Actions\HumanResources\JobPosition\SyncEmployeeJobPositions;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateEmployees;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateEmployees;
use App\Actions\SysAdmin\User\StoreUser;
use App\Actions\Traits\WithPreparePositionsForValidation;
use App\Actions\Traits\WithReorganisePositions;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Organisation;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class StoreEmployee extends OrgAction
{
    use WithPreparePositionsForValidation;
    use WithReorganisePositions;

    public function handle(Organisation|Workplace $parent, array $modelData): Employee
    {

        if (class_basename($parent) === 'Workplace') {
            $organisation = $parent->organisation;
            data_set($modelData, 'organisation_id', $organisation->id);
        } else {
            $organisation = $parent;
        }

        data_set($modelData, 'group_id', $organisation->group_id);

        $credentials = Arr::only($modelData, ['username', 'password', 'reset_password', 'user_model_status']);

        Arr::forget($modelData, ['username', 'password', 'reset_password', 'user_model_status']);

        $positions = Arr::get($modelData, 'positions', []);
        data_forget($modelData, 'positions');
        $positions = $this->reorganisePositionsSlugsToIds($positions);


        /** @var Employee $employee */
        $employee = $parent->employees()->create($modelData);
        $employee->stats()->create();

        if (in_array($employee->state, [EmployeeStateEnum::LEAVING, EmployeeStateEnum::WORKING])) {
            SetEmployeePin::make()->action($employee, updateQuietly: true);
        }


        if (Arr::get($credentials, 'username')) {

            StoreUser::make()->action(
                $employee,
                [
                    'username'          => Arr::get($credentials, 'username'),
                    'password'          => Arr::get(
                        $credentials,
                        'password',
                        (app()->isLocal() ? 'hello' : wordwrap(Str::random(), 4, '-', true))
                    ),
                    'contact_name'      => $employee->contact_name,
                    'email'             => $employee->work_email,
                    'reset_password'    => Arr::get($credentials, 'reset_password', true),
                    'status'            => true,
                    'user_model_status' => Arr::get($credentials, 'user_model_status', true),
                ],
            );
        }

        SyncEmployeeJobPositions::run($employee, $positions);

        EmployeeHydrateWeekWorkingHours::dispatch($employee);
        GroupHydrateEmployees::dispatch($employee->group);
        OrganisationHydrateEmployees::dispatch($organisation);
        EmployeeRecordSearch::dispatch($employee);

        return $employee;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }


    public function prepareForValidation(ActionRequest $request): void
    {
        $this->preparePositionsForValidation();
        if (!$this->get('username')) {
            $this->set('username', null);
        }
    }

    public function rules(): array
    {
        $rules = [
            'worker_number'                           => [
                'required',
                'max:64',
                'alpha_dash',
                new IUnique(
                    table: 'employees',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->organisation->id],
                    ]
                ),

            ],
            'employment_start_at'                     => ['sometimes', 'nullable', 'date'],
            'work_email'                              => [
                'sometimes',
                'nullable',
                'email',
                new IUnique(
                    table: 'employees',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->organisation->id],
                    ]
                )
            ],
            'alias'                                   => [
                'required',
                'string',
                'max:24',
                new IUnique(
                    table: 'employees',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->organisation->id],
                    ]
                ),
            ],
            'contact_name'                            => ['required', 'string', 'max:256'],
            'date_of_birth'                           => ['sometimes', 'nullable', 'date', 'before_or_equal:today'],
            'job_title'                               => ['sometimes', 'nullable', 'string', 'max:256'],
            'state'                                   => ['required', Rule::enum(EmployeeStateEnum::class)],
            'positions'                               => ['sometimes', 'array'],
            'positions.*.slug'                        => ['sometimes', 'string'],
            'positions.*.scopes'                      => ['sometimes', 'array'],
            'positions.*.scopes.organisations.slug.*' => ['sometimes', Rule::exists('organisations', 'slug')->where('group_id', $this->organisation->group_id)],
            'positions.*.scopes.warehouses.slug.*'    => ['sometimes', Rule::exists('warehouses', 'slug')->where('organisation_id', $this->organisation->id)],
            'positions.*.scopes.fulfilments.slug.*'   => ['sometimes', Rule::exists('fulfilments', 'slug')->where('organisation_id', $this->organisation->id)],
            'positions.*.scopes.shops.slug.*'         => ['sometimes', Rule::exists('shops', 'slug')->where('organisation_id', $this->organisation->id)],
            'email'                                   => ['sometimes', 'nullable', 'email'],
            'username'                                => [
                'nullable',
                new AlphaDashDot(),
                new IUnique(
                    table: 'users',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->organisation->group_id],
                    ]
                )
            ],
            'password'                                => ['exclude_if:username,null', 'required', 'max:255', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
            'reset_password'                          => ['exclude_if:username,null', 'sometimes', 'boolean'],
            'user_model_status'                       => ['exclude_if:username,null', 'sometimes', 'boolean'],
        ];

        if (!$this->strict) {
            $rules['created_at'] = ['sometimes', 'date'];
            $rules['fetched_at'] = ['sometimes', 'date'];
            $rules['deleted_at'] = ['sometimes', 'nullable', 'date'];
            $rules['source_id']  = ['sometimes', 'string', 'max:255'];
        }

        return $rules;
    }

    public function action(Organisation|Workplace $parent, array $modelData, int $hydratorsDelay = 0, bool $strict = true): Employee
    {

        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        if (class_basename($parent) === 'Workplace') {
            $organisation = $parent->organisation;
        } else {
            $organisation = $parent;
        }


        $this->initialisation($organisation, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

    public function asController(Organisation $organisation, ActionRequest $request): Employee
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation, $this->validatedData);
    }

    public function htmlResponse(Employee $employee): RedirectResponse
    {
        return Redirect::route('grp.org.hr.employees.show', [
            'organisation' => $employee->organisation->slug,
            'employee'     => $employee->slug,
        ]);
    }
}
