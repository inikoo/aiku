<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:24:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateUsers;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\SysAdmin\User;
use App\Rules\AlphaDashDot;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class UpdateUser
{
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(User $user, array $modelData): User
    {
        $user= $this->update($user, $modelData, ['profile', 'settings']);

        if($user->wasChanged('status')) {
            GroupHydrateUsers::run($user->group);
        }

        return $user;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return  $request->user()->hasPermissionTo('sysadmin.edit');

    }

    public function rules(): array
    {
        return [
            'username' => ['sometimes', 'required', new AlphaDashDot(), 'unique:App\Models\SysAdmin\SysUser,username'],
            'password' => ['sometimes', 'required', app()->isLocal()  || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
            'email'    => 'sometimes|required|email|unique:App\Models\SysAdmin\GroupUser,email'
        ];
    }


    public function afterValidator(Validator $validator, ActionRequest $request): void
    {
        if ($request->exists('username') and $request->get('username') != strtolower($request->get('username'))) {
            $validator->errors()->add('invalid_username', 'Username must be lowercase.');
        }
    }


    public function asController(User $user, ActionRequest $request): User
    {
        $request->validate();
        return $this->handle($user, $request->validated());
    }

    public function action(User $user, $objectData): User
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($user, $validatedData);

    }

    public function jsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }
}
