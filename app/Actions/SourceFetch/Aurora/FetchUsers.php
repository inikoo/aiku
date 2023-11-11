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
use App\Actions\Auth\User\UserSyncRoles;
use App\Enums\Auth\User\UserAuthTypeEnum;
use App\Models\Auth\GroupUser;
use App\Models\Auth\User;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FetchUsers extends FetchAction
{
    public string $commandSignature = 'fetch:users {tenants?*} {--s|source_id=} {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?User
    {
        if ($userData = $organisationSource->fetchuser($organisationSourceId)) {
            if ($user = User::withTrashed()->where('source_id', $userData['user']['source_id'])->first()) {

                if($user->auth_type==UserAuthTypeEnum::DEFAULT) {
                    Arr::forget($userData, 'auth_type');
                    Arr::forget($userData, 'legacy_password');
                }

                $user = UpdateUser::run($user, $userData['user']);
            } else {
                $groupUser = GroupUser::where('username', $userData['user']['username'])->first();
                if (!$groupUser) {
                    $groupUser = StoreGroupUser::run(
                        [
                            'username'        => $userData['user']['username'],
                            'legacy_password' => $userData['user']['legacy_password'],
                            'contact_name'    => $userData['user']['contact_name'],
                            'auth_type'       => UserAuthTypeEnum::AURORA


                        ]
                    );
                }

                $user = StoreUser::run(parent: $userData['parent'], groupUser: $groupUser, objectData: [
                    'source_id' => $userData['user']['source_id'],
                    'auth_type' => UserAuthTypeEnum::AURORA
                ]);


                DB::connection('aurora')->table('User Dimension')
                    ->where('User Key', $user->source_id)
                    ->update(['aiku_id' => $user->id]);
            }

            UserSyncRoles::make()->action($user, $userData['roles']);
            foreach ($userData['profile_images'] as $photoData) {
                $this->saveGroupImage(
                    model: $user->groupUser,
                    imageData: $photoData,
                    imageField: 'avatar_id',
                    mediaCollection: 'profile'
                );
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
