<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 05 Mar 2023 02:41:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\SysAdmin\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UserHydrateTenants implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(User $user): void
    {
        $numberOtherTenants       = DB::table('central.central_user_tenant')->where('central_user_id', $user->central_user_id)->whereNot('tenant_id', app('currentTenant')->id)->count();
        $numberOtherActiveTenants = DB::table('central.central_user_tenant')->leftJoin('central.tenants', 'tenants.id', 'tenant_id')->where('central_user_id', $user->central_user_id)
            ->whereNot('tenant_id', app('currentTenant')->id)
            ->where('tenants.status', true)
            ->count();

        $stats=[
            'number_other_tenants'        => $numberOtherTenants,
            'number_other_active_tenants' => $numberOtherActiveTenants
        ];

        $user->stats()->update($stats);
    }

    public function getJobUniqueId(User $user): string
    {
        return $user->id;
    }
}
