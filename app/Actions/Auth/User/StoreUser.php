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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Illuminate\Validation\Validator;

class StoreUser
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    private ?GroupUser $groupUser;


    /**
     * @throws \Throwable
     */
    public function handle(Guest|Employee $parent, ?GroupUser $groupUser, array $objectData = []): User|ValidationException
    {
        if (!$groupUser) {
            $groupUser = StoreGroupUser::run($objectData);
        }

        $tenant = app('currentTenant');

        $user = DB::transaction(function () use ($groupUser, $tenant, $parent) {
            /** @var \App\Models\Auth\User $user */
            $user = $parent->user()->create(
                [
                    'group_user_id' => $groupUser->id,
                    'username'      => $groupUser->username,
                    'password'      => $groupUser->password,
                    'data->avatar'  => $groupUser->media_id
                ]
            );
            $groupUser->tenants()
                ->attach($tenant->id, [
                    'user_id' => $user->id,
                    'data'    => [
                        'name' => $parent->name,
                    ]
                ]);
            $user->stats()->create();

            return $user;
        });

        if ($groupUser->avatar) {
            $groupUser->avatar->tenants()->attach($tenant->id);
        }

        $groupUser->refresh();
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

    public function afterValidator(Validator $validator): void
    {
        if ($this->groupUser && $this->groupUser->tenants()->where('tenant_id', app('currentTenant')->id)->exists()) {
            $validator->errors()->add('tenant', 'Group-user only can add one user per tenant');
        }
    }


    /**
     * @throws \Throwable
     */
    public function action(Guest|Employee $parent, ?GroupUser $groupUser, array $objectData): User|ValidationException
    {
        $this->asAction  = true;
        $this->groupUser = $groupUser;
        $this->setRawAttributes($groupUser ? [] : $objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $groupUser, $validatedData);
    }


}
