<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\GroupUser;

use App\Actions\Auth\GroupUser\UpdateGroupUser;
use App\Actions\Auth\User\UpdateUserStatus;
use App\Actions\WithActionUpdate;
use App\Models\Auth\GroupUser;
use App\Models\Auth\User;
use App\Rules\AlphaDashDot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property \App\Models\Auth\User $user
 */
class UpdateGroupUserStatus
{
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(GroupUser $groupUser, array $modelData): GroupUser
    {
        $users = $groupUser->users()->get();

        foreach ($users as $user) {
            UpdateUserStatus::run($user, $modelData);
        }

        return $this->update($groupUser, $modelData);
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
            'status' => ['sometimes', 'required', 'boolean']
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

    public function htmlResponse(User $user): RedirectResponse
    {
        return Redirect::route('account.users.edit', $user->id);
    }
}
