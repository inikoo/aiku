<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 02 Mar 2023 19:02:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant\Hydrators;


use App\Models\Central\Tenant;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;


class TenantHydrateUsers implements ShouldBeUnique
{

    use AsAction;

    public function handle(Tenant $tenant): void
    {
        $numberUsers       = DB::table('users')->count();
        $numberActiveUsers = DB::table('users')->where('status', true)->count();


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
            DB::table('users')
                ->selectRaw('LOWER(parent_type) as parent_type, count(*) as total')
                ->where('status', true)
                ->groupBy('parent_type')
                ->get() as $row
        ) {
            $stats['number_users_type_'.$row->parent_type] = Arr::get($row->total, $row->parent_type, 0);
        }

        $tenant->stats->update($stats);
    }

    public function getJobUniqueId(Tenant $tenant): string
    {
        return $tenant->id;
    }

    public function getJobTags(): array
    {
        /** @var Tenant $tenant */
        $tenant=app('currentTenant');
        return ['central','tenant:'.$tenant->code];
    }


}


