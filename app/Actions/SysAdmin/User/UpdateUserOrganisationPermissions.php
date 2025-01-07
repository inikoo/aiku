<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 07 Jan 2025 16:31:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\OrgAction;
use App\Actions\Traits\WithPreparePositionsForValidation;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithReorganisePositions;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateUserOrganisationPermissions extends OrgAction
{
    use WithActionUpdate;
    use WithPreparePositionsForValidation;
    use WithReorganisePositions;


    public function handle(User $user, Organisation $organisation, array $modelData): User
    {
        $permissions = Arr::get($modelData, 'permissions', []);
        $positions = $this->reorganisePositionsSlugsToIds($permissions);

        $user->syncPermissions($positions);

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
            'permissions' => ['sometimes', 'array'],
        ];
    }

    public function action(User $user, Organisation $organisation, $modelData): User
    {
        $this->asAction     = true;
        $this->organisation = $organisation;

        $this->initialisation($organisation, $modelData);

        return $this->handle($user, $organisation, $this->validatedData);
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->preparePositionsForValidation();
        //todo check if is a valid current model (gust|employee) in user_has_models  if is an active one reject


    }

    public function asController(User $user, Organisation $organisation, ActionRequest $request): User
    {

        $this->initialisation($organisation, $request);

        return $this->handle($user, $organisation, $this->validatedData);
    }

    public function jsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }
}
