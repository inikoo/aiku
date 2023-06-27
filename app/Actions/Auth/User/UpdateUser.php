<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User;

use App\Actions\Auth\GroupUser\UpdateGroupUser;
use App\Actions\WithActionUpdate;
use App\Enums\Auth\User\SynchronisableUserFieldsEnum;
use App\Models\Auth\User;
use App\Rules\AlphaDashDot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class UpdateUser
{
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(User $user, array $modelData): User
    {
        UpdateGroupUser::run(
            $user->groupUser,
            Arr::only($modelData, SynchronisableUserFieldsEnum::values())
        );

        $user->refresh();
        return $this->update($user, Arr::except($modelData, SynchronisableUserFieldsEnum::values()), ['profile', 'settings']);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return  $request->user()->can('sysadmin.edit');

    }

    public function rules(): array
    {
        return [
            'username' => ['sometimes', 'required', new AlphaDashDot(), 'unique:App\Models\SysAdmin\SysUser,username'],
            'password' => ['sometimes', 'required', app()->isLocal()  || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
            'email'    => 'sometimes|required|email|unique:App\Models\Auth\GroupUser,email'
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

    public function htmlResponse(User $user): RedirectResponse
    {
        return Redirect::route('sysadmin.users.edit', $user->username);
    }
}
