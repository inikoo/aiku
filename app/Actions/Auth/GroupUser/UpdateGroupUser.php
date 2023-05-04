<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 02 May 2023 10:48:12 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\GroupUser;

use App\Actions\WithActionUpdate;
use App\Enums\Auth\SynchronisableUserFields;
use App\Models\Auth\GroupUser;
use App\Rules\AlphaDashDot;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property \App\Models\Auth\User $user
 */
class UpdateGroupUser
{
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(GroupUser $groupUser, array $modelData): GroupUser
    {

        if(isset($modelData['password'])) {
            $modelData['password'] = Hash::make($modelData['password']);
        }
        $updatedGroupUser = $this->update($groupUser, $modelData);

        foreach ($groupUser->users as $user) {
            $this->update($user, Arr::only($modelData, SynchronisableUserFields::values()));
        }

        return $updatedGroupUser;
    }



    public function rules(): array
    {
        return [
            'username' => ['sometimes', new AlphaDashDot(), 'unique:App\Models\Auth\GroupUser,username'],
            'password' => ['sometimes', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
            'email'    => ['sometimes', 'email', 'unique:App\Models\SysAdmin\SysUser,email']
        ];
    }


    public function afterValidator(Validator $validator, ActionRequest $request): void
    {
        if ($request->exists('username') and $request->get('username') != strtolower($request->get('username'))) {
            $validator->errors()->add('invalid_username', 'Username must be lowercase.');
        }
    }

    public function asController(GroupUser $groupUser, ActionRequest $request): GroupUser
    {
        return $this->handle($groupUser, $request->validated());
    }

    public function action(GroupUser $groupUser, $objectData): GroupUser
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($groupUser, $validatedData);
    }
}
