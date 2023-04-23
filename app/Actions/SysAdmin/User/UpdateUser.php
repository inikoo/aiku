<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Sept 2022 15:34:51 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\WithActionUpdate;
use App\Models\Auth\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property \App\Models\Auth\User $user
 */
class UpdateUser
{
    use WithActionUpdate;

    public function handle(User $user, array $modelData): User
    {
        return $this->update($user, $modelData, ['profile', 'settings']);
    }


    public function authorize(User $user, ActionRequest $request): bool
    {
        if ($user->id == $request->user()) {
            return true;
        }

        return false;
    }


    public function rules(): array
    {
        return [
            'username' => 'sometimes|required|alpha_dash|unique:App\Models\Auth\User,username',
            'password' => ['required', Password::min(8)->uncompromised()],
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
        return $this->handle($user, $request->validated());
    }

    public function htmlResponse(User $user): RedirectResponse
    {
        return Redirect::route('account.users.edit', $user->id);
    }
}
