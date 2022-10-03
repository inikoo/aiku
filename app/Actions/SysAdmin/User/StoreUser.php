<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Sept 2022 15:04:34 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;


use App\Actions\Central\Tenant\HydrateTenant;
use App\Models\Central\CentralUser;
use App\Models\Central\Tenant;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;


class StoreUser
{
    use AsAction;

    public function handle(Tenant $tenant, Guest|Employee $parent, CentralUser $centralUser): User
    {
        tenancy()->central(function () use ($centralUser, $tenant) {
            $centralUser->tenants()->attach($tenant);
        });
        $user = User::where('global_id', $centralUser->global_id)->first();
        $user->parent()->associate($parent);
        $user->name=$parent->name;
        $user->save();
        /** Run Hydrate here because boot() static::created is not call in User.php
         because tenancy package Synced resources between tenants
         * https://tenancyforlaravel.com/docs/v3/synced-resources-between-tenants
         */
        HydrateTenant::make()->userStats();

        return $user;
    }


}
