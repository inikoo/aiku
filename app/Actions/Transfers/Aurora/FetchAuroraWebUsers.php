<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 04 Nov 2022 14:51:04 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\CRM\WebUser\StoreWebUser;
use App\Actions\CRM\WebUser\UpdateWebUser;
use App\Models\CRM\WebUser;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraWebUsers extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:web-users {organisations?*} {--s|source_id=} {--S|shop= : Shop slug} {--N|only_new : Fetch only new} {--d|db_suffix=} {--r|reset}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?WebUser
    {
        if ($webUserData = $organisationSource->fetchWebUser($organisationSourceId)) {
            if ($webUserData['customer']) {
                if ($webUser = WebUser::withTrashed()->where('source_id', $webUserData['webUser']['source_id'])
                    ->first()) {
                    try {
                        $webUser = UpdateWebUser::make()->action($webUser, $webUserData['webUser'], 60, false);
                        $this->recordChange($organisationSource, $webUser->wasChanged());
                    } catch (Exception $e) {
                        $this->recordError($organisationSource, $e, $webUserData['webUser'], 'WebUser', 'update');

                        return null;
                    }
                } else {
                    try {
                        $webUser = StoreWebUser::make()->action($webUserData['customer'], $webUserData['webUser'], 60, false);
                        $this->recordNew($organisationSource);
                    } catch (Exception $e) {
                        $this->recordError($organisationSource, $e, $webUserData['webUser'], 'WebUser', 'store');

                        return null;
                    }
                }

                $sourceData = explode(':', $webUser->source_id);
                DB::connection('aurora')->table('Website User Dimension')
                    ->where('Website User Key', $sourceData[1])
                    ->update(['aiku_id' => $webUser->id]);

                return $webUser;
            } else {
                print "Warning web user $organisationSourceId do not have customer\n";
            }
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Website User Dimension')
            ->select('Website User Key as source_id')
            ->orderBy('source_id');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        if ($this->shop) {
            $sourceData = explode(':', $this->shop->website->source_id);
            $query->where('Website User Website Key', $sourceData[1]);
        }

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Website User Dimension');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }
        if ($this->shop) {
            $sourceData = explode(':', $this->shop->website->source_id);
            $query->where('Website User Website Key', $sourceData[1]);
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Website User Dimension')->update(['aiku_id' => null]);
    }
}
