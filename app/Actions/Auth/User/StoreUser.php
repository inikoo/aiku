<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User;

use App\Actions\Auth\GroupUser\Hydrators\GroupUserHydrateTenants;
use App\Actions\Auth\GroupUser\StoreGroupUser;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateUsers;
use App\Models\Auth\GroupUser;
use App\Models\Auth\Guest;
use App\Models\Auth\User;
use App\Models\HumanResources\Employee;
use App\Rules\AlphaDashDot;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreUser
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;
    /**
     * @var \App\Models\Auth\GroupUser|null
     */
    private ?GroupUser $groupUser;

    public function handle(Guest|Employee $parent, ?GroupUser $groupUser, array $objectData=[]): User
    {
        if (!$groupUser) {
            $groupUser = StoreGroupUser::run($objectData);
        }

        $tenant = app('currentTenant');


        /** @var \App\Models\Auth\User $user */
        $user = $parent->user()->create(
            [
                'group_user_id' => $groupUser->id,
                'tenant_id'     => $tenant->id,
                'username'      => $groupUser->username,
                'password'      => $groupUser->password,
                'data->avatar'  => $groupUser->media_id
            ]
        );
        $user->stats()->create();
        if ($groupUser->avatar) {
            $groupUser->avatar->tenants()->attach($tenant->id);
        }


        TenantHydrateUsers::run($tenant);
        GroupUserHydrateTenants::run($groupUser);


        return $user;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("shops.customers.edit");
    }

    public function rules(): array
    {
        if ($this->groupUser) {
            return [];
        } else {
            return [
                'username' => ['required', new AlphaDashDot(), 'unique:App\Models\SysAdmin\SysUser,username'],
                'password' => ['required', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
                'email'    => ['required', 'email', 'unique:App\Models\SysAdmin\SysUser,email']
            ];
        }
    }

    public function action(Guest|Employee $parent, ?GroupUser $groupUser, array $objectData): User
    {
        $this->asAction  = true;
        $this->groupUser = $groupUser;
        $this->setRawAttributes($groupUser ? [] : $objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $groupUser, $validatedData);
    }


}
