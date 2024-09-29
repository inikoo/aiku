<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 28 Sept 2024 10:44:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\SysAdmin\User\StoreUser;
use App\Actions\SysAdmin\User\UpdateUser;
use App\Models\SysAdmin\User;
use App\Transfers\SourceOrganisationService;
use Arr;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraDeletedUsers extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:deleted-users {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?User
    {
        if ($userData = $organisationSource->fetchDeletedUser($organisationSourceId)) {
            if ($userData['user']) {
                if ($user = User::withTrashed()->where('source_id', $userData['user']['source_id'])
                    ->first()) {
                    if (Arr::get($user->data, 'deleted.source') == 'aurora') {
                        try {
                            $user = UpdateUser::make()->action(
                                user: $user,
                                modelData: $userData['user'],
                                hydratorsDelay: 60,
                                strict: false,
                                audit: false
                            );
                            $this->recordChange($organisationSource, $user->wasChanged());
                        } catch (Exception $e) {
                            $this->recordError($organisationSource, $e, $userData['user'], 'DeletedUser', 'update');

                            return null;
                        }
                    }
                } else {
                    try {
                        $user = StoreUser::make()->action(
                            parent: $userData['shop'],
                            modelData: $userData['user'],
                            hydratorsDelay: $this->hydrateDelay,
                            strict: false
                        );

                        $this->recordNew($organisationSource);
                    } catch (Exception $e) {
                        $this->recordError($organisationSource, $e, $userData['user'], 'DeletedUser', 'store');

                        return null;
                    }
                }

                $sourceData = explode(':', $user->source_id);
                DB::connection('aurora')->table('User Deleted Dimension')
                    ->where('User Key', $sourceData[1])
                    ->update(['aiku_id' => $user->id]);

                return $user;
            }
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('User Deleted Dimension')
            ->select('User Deleted Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('User Deleted Dimension');
        return $query->count();
    }


}
