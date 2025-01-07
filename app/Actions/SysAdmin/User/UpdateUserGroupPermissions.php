<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Sept 2024 18:51:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\OrgAction;
use App\Actions\Traits\WithPreparePositionsForValidation;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithReorganisePositions;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateUserGroupPermissions extends OrgAction
{
    use WithActionUpdate;
    use WithPreparePositionsForValidation;
    use WithReorganisePositions;

    protected bool $asAction = false;



    public function handle(User $user, array $modelData): User
    {
        $permissions = Arr::get($modelData, 'permissions', []);

        $positions = $this->reorganiseGroupPositionsSlugsToIds($permissions);

        $user->ad($positions);

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

    public function action(User $user, $modelData): User
    {
        $this->asAction     = true;

        $this->initialisationFromGroup($user->group, $modelData);

        return $this->handle($user, $this->validatedData);
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->preparePositionsForValidation();
        //todo check if is a valid current model (gust|employee) in user_has_models  if is an active one reject

    }

    public function asController(User $user, ActionRequest $request): User
    {
        $this->initialisationFromGroup(app('group'), $request);
        return $this->handle($user, $this->validatedData);
    }

    public function jsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }
}
