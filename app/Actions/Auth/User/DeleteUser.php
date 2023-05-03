<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 03 May 2023 08:56:14 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User;

use App\Actions\WithActionUpdate;
use App\Models\Auth\GroupUser;
use App\Models\Auth\User;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteUser
{
    use AsAction;
    use WithActionUpdate;

    private bool $trusted = false;

    public function handle(User $user): bool
    {
        $this->update($user, [
            'username' => $user->username . '@deleted-' . $user->id
        ]);

        return $user->delete();
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->trusted) {
            return true;
        }

        return $request->user()->hasPermissionTo("sysadmin.edit");
    }

    public function asController(User $user, ActionRequest $request): bool
    {
        return $this->handle($user);
    }

    public function action(User $user): bool
    {
        return $this->handle($user);
    }

}
