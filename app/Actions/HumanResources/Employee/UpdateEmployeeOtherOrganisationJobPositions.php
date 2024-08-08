<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 00:49:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\GrpAction;
use App\Actions\HumanResources\Employee\Search\EmployeeRecordSearch;
use App\Actions\HumanResources\Employee\Traits\HasEmployeePositionGenerator;
use App\Actions\HumanResources\JobPosition\SyncEmployableJobPositions;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateEmployees;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateEmployees;
use App\Actions\SysAdmin\User\UpdateUser;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Enums\SysAdmin\User\UserAuthTypeEnum;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\User;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use App\Rules\PinRule;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class UpdateEmployeeOtherOrganisationJobPositions extends GrpAction
{
    use WithActionUpdate;
    use HasPositionsRules;
    use HasEmployeePositionGenerator;

    protected bool $asAction = false;

    private Employee $employee;

    public function handle(User $user, array $modelData): User
    {
        $employee = $user->parent;
        if (Arr::exists($modelData, 'positions')) {
            $jobPositions = $this->generatePositions($modelData);

            SyncEmployableJobPositions::run($employee, $jobPositions);
            Arr::forget($modelData, 'positions');
        }

        return $user;
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
            'positions'                             => ['sometimes', 'array'],
            'positions.*.slug'                      => ['sometimes', 'string'],
            'positions.*.scopes'                    => ['sometimes', 'array'],
            'positions.*.scopes.warehouses.slug.*'  => ['sometimes', Rule::exists('warehouses', 'slug')->where('organisation_id', $this->organisation->id)],
            'positions.*.scopes.fulfilments.slug.*' => ['sometimes', Rule::exists('fulfilments', 'slug')->where('organisation_id', $this->organisation->id)],
            'positions.*.scopes.shops.slug.*'       => ['sometimes', Rule::exists('shops', 'slug')->where('organisation_id', $this->organisation->id)],
        ];

    }

    public function action(User $user, $modelData): User
    {
        $this->asAction = true;
        $this->user = $user;

        $this->initialisation($user->group, $modelData);

        return $this->handle($user, $this->validatedData);
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if(!$this->user->parent instanceof Employee)
        {
            abort(419);
        }
    }

    public function asController(User $user, ActionRequest $request): User
    {
        $this->user = $user;
        $this->initialisation(app('group'), $request);

        return $this->handle($user, $this->validatedData);
    }

    public function jsonResponse(User $user): EmployeeResource
    {
        return new EmployeeResource($user);
    }
}
