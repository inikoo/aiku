<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Tenant\Hydrators;

use App\Models\Auth\User;
use App\Models\Tenancy\Tenant;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class TenantHydrateUsers implements ShouldBeUnique
{
    use AsAction;
    use HasTenantHydrate;

    public function handle(Tenant $tenant): void
    {
        $numberUsers       = User::count();
        $numberActiveUsers = User::where('status', true)->count();


        $stats = [
            'number_users'                 => $numberUsers,
            'number_users_status_active'   => $numberActiveUsers,
            'number_users_status_inactive' => $numberUsers - $numberActiveUsers

        ];


        foreach (
            ['employee', 'guest', 'supplier', 'agent', 'customer']
            as $userType
        ) {
            $stats['number_users_type_'.$userType] = 0;
        }

        foreach (
            DB::connection('tenant')->table('users')
                ->selectRaw('LOWER(parent_type) as parent_type, count(*) as total')
                ->where('status', true)
                ->groupBy('parent_type')
                ->get() as $row
        ) {
            $stats['number_users_type_'.$row->parent_type] = Arr::get($row->total, $row->parent_type, 0);
        }

        $tenant->stats->update($stats);
    }
}
