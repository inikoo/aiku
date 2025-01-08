<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Sept 2024 18:51:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\GrpAction;
use App\Actions\HumanResources\JobPosition\SyncUserJobPositions;
use App\Actions\Traits\WithPreparePositionsForValidation;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithReorganisePositions;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateUsersPseudoJobPositions extends GrpAction
{
    use WithActionUpdate;
    use WithPreparePositionsForValidation;
    use WithReorganisePositions;

    protected bool $asAction = false;

    private Employee $employee;

    private User $user;


    private Organisation $organisation;

    public function handle(User $user, array $modelData): User
    {
        $positions = Arr::get($modelData, 'positions', []);
        $positions = $this->reorganisePositionsSlugsToIds($positions);

        //todo , this is attach no sync
        //https://github.com/inikoo/aiku/issues/941
        SyncUserJobPositions::run($user, $positions);

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
            'positions.*.slug'                      => ['sometimes', 'string'],
            'positions.*.scopes'                    => ['sometimes', 'array'],
            'positions.*.scopes.warehouses.slug.*'  => ['sometimes', Rule::exists('warehouses', 'slug')->where('organisation_id', $this->organisation->id)],
            'positions.*.scopes.fulfilments.slug.*' => ['sometimes', Rule::exists('fulfilments', 'slug')->where('organisation_id', $this->organisation->id)],
            'positions.*.scopes.shops.slug.*'       => ['sometimes', Rule::exists('shops', 'slug')->where('organisation_id', $this->organisation->id)],
        ];
    }

    public function action(User $user, Organisation $organisation, $modelData): User
    {
        $this->asAction     = true;
        $this->user         = $user;
        $this->organisation = $organisation;

        $this->initialisation($user->group, $modelData);

        return $this->handle($user, $this->validatedData);
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->preparePositionsForValidation();
        //todo check if is a valid current model (gust|employee) in user_has_models  if is an active one reject


    }

    public function asController(User $user, Organisation $organisation, ActionRequest $request): User
    {
        $this->user         = $user;
        $this->organisation = $organisation;

        $this->initialisation(app('group'), $request);

        return $this->handle($user, $this->validatedData);
    }

    public function jsonResponse(User $user): EmployeeResource
    {
        return new EmployeeResource($user);
    }
}
