<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\GroupUser;

use App\Actions\WithActionUpdate;
use App\Models\Auth\GroupUser;
use App\Models\Auth\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property \App\Models\Auth\User $user
 */
class UpdateGroupUserStatus
{
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(GroupUser $groupUser, bool $status): GroupUser
    {
        $groupUser->update(
            [
                'status' => $status
            ]
        );

        if (!$status) {
            foreach ($groupUser->tenants as $tenant) {
                $userID = $tenant->pivot->user_id;
                $tenant->execute(
                    function () use ($userID, $status) {
                        $user = User::find($userID);
                        $user->update([
                            'status' => $status
                        ]);
                    }
                );
            }
        }


        return $groupUser;
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

    public function action(GroupUser $groupUser, bool $status): GroupUser
    {
        $this->asAction = true;

        return $this->handle($groupUser, $status);
    }

    public function htmlResponse(User $user): RedirectResponse
    {
        return Redirect::route('account.users.edit', $user->id);
    }
}
