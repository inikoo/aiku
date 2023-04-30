<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 20 Sept 2022 22:43:30 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\CentralUser;

use App\Models\Central\CentralUser;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreCentralUser
{
    use AsAction;

    public function handle(array $modelData): CentralUser
    {
        $modelData['password']  = Hash::make($modelData['password']);
        $centralUser            = GroupUser::create($modelData);

        return SetGroupUserAvatar::run($centralUser);
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
            'username' => ['required', new AlphaDashDot(), 'unique:App\Models\SysAdmin\SysUser,username'],
            'password' => ['required', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
            'email'    => 'required|email|unique:App\Models\SysAdmin\SysUser,email',
        ];

    }

    public function action(array $objectData): GroupUser
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($validatedData);
    }
}
