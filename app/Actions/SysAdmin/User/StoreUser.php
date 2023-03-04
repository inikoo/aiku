<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Sept 2022 15:04:34 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;


use App\Actions\Central\CentralUser\Hydrators\CentralUserHydrateTenants;
use App\Actions\Central\Tenant\Hydrators\TenantHydrateUsers;
use App\Models\Central\CentralUser;
use App\Models\Central\Tenant;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;
use  Spatie\Multitenancy\Landlord;


class StoreUser
{
    use AsAction;

    public function handle(Tenant $tenant, Guest|Employee $parent, CentralUser $centralUser): User
    {
        Landlord::execute(function () use ($centralUser, $tenant) {
            $centralUser->tenants()->syncWithoutDetaching($tenant);
        });
        $user=$parent->user()->create(
            [
            'central_user_id'=>$centralUser->id,
            'username'=>$centralUser->username,
            'password'=>$centralUser->password,
            ]
        );
        $user->stats()->create();
        $user=SetAvatar::run($user);
        TenantHydrateUsers::run($tenant);
        CentralUserHydrateTenants::run($centralUser);


        return $user;
    }


}
