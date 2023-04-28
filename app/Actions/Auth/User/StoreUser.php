<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User;

use App\Actions\Central\CentralUser\Hydrators\CentralUserHydrateTenants;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateUsers;
use App\Models\Auth\Guest;
use App\Models\Auth\User;
use App\Models\Central\CentralUser;
use App\Models\HumanResources\Employee;
use App\Models\Tenancy\Tenant;
use App\Rules\AlphaDashDot;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Spatie\Multitenancy\Landlord;

class StoreUser
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    public function handle(Tenant $tenant, Guest|Employee $parent, CentralUser $centralUser): User
    {
        Landlord::execute(function () use ($centralUser, $tenant) {
            $centralUser->tenants()->syncWithoutDetaching($tenant);
        });
        /** @var \App\Models\Auth\User $user */
        $user = $parent->user()->create(
            [
                'central_user_id' => $centralUser->id,
                'username'        => $centralUser->username,
                'password'        => $centralUser->password,
                'data->avatar'    => $centralUser->media_id
            ]
        );
        $user->stats()->create();
        if ($centralUser->avatar) {
            $centralUser->avatar->tenants()->attach($tenant->id);
        }


        TenantHydrateUsers::run($tenant);
        CentralUserHydrateTenants::run($centralUser);


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
        return [
            'username' => ['sometimes', 'required', new AlphaDashDot(), 'unique:App\Models\SysAdmin\SysUser,username'],
            'password' => ['required', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],

        ];
    }

    public function action(array $objectData, Guest|Employee $parent, Tenant $tenant, CentralUser $centralUser): User
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($tenant, $parent, $centralUser);
    }
}
