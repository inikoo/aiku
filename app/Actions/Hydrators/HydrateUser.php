<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Sept 2022 21:16:11 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Hydrators;


use App\Models\SysAdmin\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class HydrateUser extends HydrateModel
{


    public string $commandSignature = 'hydrate:users {tenant_code?} {id?}';


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


