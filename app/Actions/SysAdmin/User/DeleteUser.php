<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:24:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\Traits\WithActionUpdate;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteUser
{
    use AsAction;
    use WithActionUpdate;

    private bool $trusted = false;

    public function handle(User $user): User
    {
        $this->update($user, [
            'username' => $user->username . '@deleted-' . $user->id
        ]);
        $user->delete();
        return $user;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->trusted) {
            return true;
        }

        return $request->user()->hasPermissionTo("sysadmin.edit");
    }

    public function asController(User $user, ActionRequest $request): User
    {
        return $this->handle($user);
    }

    public function action(User $user): User
    {
        return $this->handle($user);
    }

}
