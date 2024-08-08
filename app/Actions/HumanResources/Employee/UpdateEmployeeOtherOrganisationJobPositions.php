<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 00:49:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\GrpAction;
use App\Actions\HumanResources\Employee\Traits\HasEmployeePositionGenerator;
use App\Actions\HumanResources\JobPosition\SyncEmployeeOtherOrganisationJobPositions;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateEmployeeOtherOrganisationJobPositions extends GrpAction
{
    use WithActionUpdate;
    use HasPositionsRules;
    use HasEmployeePositionGenerator;

    protected bool $asAction = false;

    private Employee $employee;

    private User $user;

    private Organisation $otherOrganisation;

    public function handle(User $user, Organisation $otherOrganisation, array $modelData): User
    {
        $employee     = $user->parent;
        $jobPositions = $this->generatePositions($otherOrganisation, $modelData);

        SyncEmployeeOtherOrganisationJobPositions::run($employee, $otherOrganisation, $jobPositions);


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
            'positions.*.slug'                      => ['sometimes', 'string'],
            'positions.*.scopes'                    => ['sometimes', 'array'],
            'positions.*.scopes.warehouses.slug.*'  => ['sometimes', Rule::exists('warehouses', 'slug')->where('organisation_id', $this->otherOrganisation->id)],
            'positions.*.scopes.fulfilments.slug.*' => ['sometimes', Rule::exists('fulfilments', 'slug')->where('organisation_id', $this->otherOrganisation->id)],
            'positions.*.scopes.shops.slug.*'       => ['sometimes', Rule::exists('shops', 'slug')->where('organisation_id', $this->otherOrganisation->id)],
        ];
    }

    public function action(User $user, Organisation $otherOrganisation, $modelData): User
    {
        $this->asAction          = true;
        $this->user              = $user;
        $this->otherOrganisation = $otherOrganisation;

        $this->initialisation($user->group, $modelData);

        return $this->handle($user, $otherOrganisation, $this->validatedData);
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if (!$this->user->parent instanceof Employee) {
            abort(419);
        }
    }

    public function asController(User $user, Organisation $organisation, ActionRequest $request): User
    {
        $this->user              = $user;
        $this->otherOrganisation = $organisation;

        $this->initialisation(app('group'), $request);

        return $this->handle($user, $organisation, $this->validatedData);
    }

    public function jsonResponse(User $user): EmployeeResource
    {
        return new EmployeeResource($user);
    }
}
