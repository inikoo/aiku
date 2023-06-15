<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 02 Mar 2023 19:02:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Auth\GroupUser\StoreGroupUser;
use App\Actions\Auth\User\StoreUser;
use App\Actions\Auth\User\UpdateUser;
use App\Enums\Auth\User\UserAuthTypeEnum;
use App\Models\Auth\GroupUser;
use App\Models\Auth\User;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\NoReturn;

class FetchUsers extends FetchAction
{
    public string $commandSignature = 'fetch:users {tenants?*} {--s|source_id=} {--d|db_suffix=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?User
    {
        if ($userData = $tenantSource->fetchuser($tenantSourceId)) {
            if ($user = User::withTrashed()->where('source_id', $userData['user']['source_id'])->first()) {
                $user = UpdateUser::run($user, $userData['user']);

            } else {
                $groupUser = GroupUser::where('username', $userData['user']['username'])->first();
                if (!$groupUser) {
                    $groupUser = StoreGroupUser::run(
                        [
                            'username' => $userData['user']['username'],
                            'password' => (app()->isLocal() ? 'hello' : wordwrap(Str::random(), 4, '-', true))
                        ]
                    );
                }


                $user = StoreUser::run(parent:$userData['parent'], groupUser:$groupUser, objectData:[
                    'source_id'=> $userData['user']['source_id'],
                    'auth_type'=> UserAuthTypeEnum::AURORA
                ]);


                DB::connection('aurora')->table('User Dimension')
                    ->where('User Key', $user->source_id)
                    ->update(['aiku_id' => $user->id]);

            }


            return $user;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('User Dimension')
            ->select('User Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('User Dimension');

        return $query->count();
    }
}
