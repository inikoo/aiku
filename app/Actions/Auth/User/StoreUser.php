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
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Multitenancy\Landlord;

class StoreUser
{
    use AsAction;

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
}
