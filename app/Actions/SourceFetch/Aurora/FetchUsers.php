<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 02 Mar 2023 19:02:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;


use App\Actions\Central\CentralUser\StoreCentralUser;
use App\Actions\SysAdmin\User\StoreUser;
use App\Actions\SysAdmin\User\UpdateUser;
use App\Models\Central\CentralUser;
use App\Models\SysAdmin\User;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\NoReturn;


class FetchUsers extends FetchAction
{

    public string $commandSignature = 'fetch:users {tenants?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?User
    {

        if ($userData = $tenantSource->fetchuser($tenantSourceId)) {


            if ($user = User::withTrashed()->where('source_id', $userData['user']['source_id'])
                ->first()) {
                $user = UpdateUser::run($user, $userData['user']);
            } else {
                $centralUser = CentralUser::where('username', $userData['user']['username'])->first();
                if (!$centralUser) {


                    $centralUser = StoreCentralUser::run(
                        [
                            'username' => $userData['user']['username'],
                            'password' => (app()->isLocal() ? 'hello' : wordwrap(Str::random(), 4, '-', true))
                        ]
                    );
                }


                $user = StoreUser::run(tenant(), $userData['parent'], $centralUser);
                $user->update(
                    [
                        'source_id'=> $userData['user']['source_id']
                    ]
                );


                DB::connection('aurora')->table('User Dimension')
                    ->where('User Key', $user->source_id)
                    ->update(['aiku_id' => $user->id]);
            }


            return $user;
        }

        return null;
    }

    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('User Dimension')
            ->select('User Key as source_id')
            ->orderBy('source_id');
    }

    function count(): ?int
    {
        $query = DB::connection('aurora')->table('User Dimension');

        return $query->count();
    }

}
