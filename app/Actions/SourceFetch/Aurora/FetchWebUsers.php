<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 04 Nov 2022 14:51:04 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\CRM\WebUser\StoreWebUser;
use App\Actions\CRM\WebUser\UpdateWebUser;
use App\Models\SysAdmin\WebUser;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchWebUsers extends FetchAction
{
    public string $commandSignature = 'fetch:web-users {organisations?*} {--s|source_id=} {--S|shop= : Shop slug} {--N|only_new : Fetch only new} {--d|db_suffix=} {--r|reset}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?WebUser
    {
        if ($webUserData = $organisationSource->fetchWebUser($organisationSourceId)) {
            if ($webUserData['customer']) {
                if ($webUser = WebUser::withTrashed()->where('source_id', $webUserData['webUser']['source_id'])
                    ->first()) {
                    // print_r( $webUserData['webUser']);
                    $webUser = UpdateWebUser::make()->action($webUser, $webUserData['webUser'], 60, false);
                } else {
                    //print_r( $webUserData['webUser']);
                    $webUser = StoreWebUser::make()->action($webUserData['customer'], $webUserData['webUser'], 60, false);
                }

                $sourceData= explode(':', $webUser->source_id);
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
        $query= DB::connection('aurora')
            ->table('Website User Dimension')
            ->select('Website User Key as source_id')
            ->orderBy('source_id');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        if ($this->shop) {
            $sourceData= explode(':', $this->shop->website->source_id);
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
            $sourceData= explode(':', $this->shop->website->source_id);
            $query->where('Website User Website Key', $sourceData[1]);
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Website User Dimension')->update(['aiku_id' => null]);
    }
}
