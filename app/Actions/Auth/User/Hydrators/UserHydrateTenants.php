<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User\Hydrators;

use App\Actions\Traits\WithOrganisationJob;
use App\Models\Auth\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UserHydrateTenants implements ShouldBeUnique
{
    use AsAction;
    use WithOrganisationJob;

    public function handle(User $user): void
    {
        $numberOtherTenants       = DB::table('public.central_user_tenant')->where('central_user_id', $user->central_user_id)->whereNot('tenant_id', app('currentTenant')->id)->count();
        $numberOtherActiveTenants = DB::table('public.central_user_tenant')->leftJoin('public.tenants', 'tenants.id', 'tenant_id')->where('central_user_id', $user->central_user_id)
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
