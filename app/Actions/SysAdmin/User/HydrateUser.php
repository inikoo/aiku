<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:37:54 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;


use App\Actions\HydrateModel;
use App\Models\SysAdmin\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class HydrateUser extends HydrateModel
{


    public string $commandSignature = 'hydrate:users {tenants?*} {--i|id=}';


    public function handle(User $user): void
    {
        $this->tenantStats($user);
    }

    public function tenantStats(User $user)
    {
        $numberOtherTenants       = DB::table('central.tenant_users')->where('global_user_id', $user->global_id)->whereNot('tenant_id', tenant('id'))->count();
        $numberOtherActiveTenants = DB::table('central.tenant_users')->where('global_user_id', $user->global_id)
            ->whereNot('tenant_id', tenant('id'))
            ->where('status', true)
            ->count();


        $user->update(
            [
                'data' => [
                    'number_other_tenants'        => $numberOtherTenants,
                    'number_other_active_tenants' => $numberOtherActiveTenants
                ]
            ]
        );
    }

    protected function getModel(int $id): User
    {
        return User::findOrFail($id);
    }

    protected function getAllModels(): Collection
    {
        return User::all();
    }


}


