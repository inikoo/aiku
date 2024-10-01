<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Sept 2024 18:51:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\GrpAction;
use App\Actions\HumanResources\Employee\HasPositionsRules;
use App\Actions\HumanResources\Employee\Traits\HasEmployeePositionGenerator;
use App\Actions\HumanResources\JobPosition\SyncUserJobPositions;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateUsersPseudoJobPositions extends GrpAction
{
    use WithActionUpdate;
    use HasPositionsRules;
    use HasEmployeePositionGenerator;

    protected bool $asAction = false;

    private Employee $employee;

    private User $user;


    private Organisation $organisation;

    public function handle(User $user, Organisation $organisation, array $modelData): User
    {
        $jobPositions = $this->generatePositions($organisation, $modelData);


        SyncUserJobPositions::run($user, $jobPositions);

        $user->refresh();

        return $user;
    }


    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("sysadmin.edit");
    }

    public function rules(): array
    {
        return [
            'positions'                             => ['sometimes', 'array'],
            'positions.*.code'                      => ['sometimes', 'string'],
            'positions.*.scopes'                    => ['sometimes', 'array'],
            'positions.*.scopes.warehouses.slug.*'  => ['sometimes', Rule::exists('warehouses', 'slug')->where('organisation_id', $this->organisation->id)],
            'positions.*.scopes.fulfilments.slug.*' => ['sometimes', Rule::exists('fulfilments', 'slug')->where('organisation_id', $this->organisation->id)],
            'positions.*.scopes.shops.slug.*'       => ['sometimes', Rule::exists('shops', 'slug')->where('organisation_id', $this->organisation->id)],
        ];
    }

    public function action(User $user, Organisation $otherOrganisation, $modelData): User
    {
        $this->asAction          = true;
        $this->user              = $user;
        $this->organisation = $otherOrganisation;

        $this->initialisation($user->group, $modelData);

        return $this->handle($user, $otherOrganisation, $this->validatedData);
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->preparePositionsForValidation();
        if ($this->user->employees()->where('user_has_models.organisation_id', $this->organisation->id)->exists()) {
            abort(419);
        }
    }

    public function asController(User $user, Organisation $organisation, ActionRequest $request): User
    {
        $this->user              = $user;
        $this->organisation = $organisation;

        $this->initialisation(app('group'), $request);

        return $this->handle($user, $organisation, $this->validatedData);
    }

    public function jsonResponse(User $user): EmployeeResource
    {
        return new EmployeeResource($user);
    }
}
