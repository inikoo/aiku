<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 04 Nov 2022 14:51:04 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;


use App\Actions\Web\WebUser\StoreWebUser;
use App\Actions\Web\WebUser\UpdateWebUser;
use App\Models\Web\WebUser;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchWebUsers extends FetchAction
{

    public string $commandSignature = 'fetch:web-users {tenants?*} {--s|source_id=} {--S|shop= : Shop slug}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?WebUser
    {
        if ($webUserData = $tenantSource->fetchWebUser($tenantSourceId)) {

            if($webUserData['customer']) {
                if ($webUser = WebUser::withTrashed()->where('source_id', $webUserData['webUser']['source_id'])
                    ->first()) {
                    $webUser = UpdateWebUser::run($webUser, $webUserData['webUser']);
                } else {
                    $webUser = StoreWebUser::run($webUserData['customer'], $webUserData['webUser']);
                }

                DB::connection('aurora')->table('Website User Dimension')
                    ->where('Website User Key', $webUser->source_id)
                    ->update(['aiku_id' => $webUser->id]);

                return $webUser;
            }else{
                print "Warning web user $tenantSourceId do not have customer\n";
            }
        }

        return null;
    }

    function getModelsQuery(): Builder
    {
        $query= DB::connection('aurora')
            ->table('Website User Dimension')
            ->select('Website User Key as source_id')
            ->orderBy('source_id');
        if ($this->shop) {
            $query->where('Website User Website Key', $this->shop->website->source_id);
        }

        return $query;
    }

    function count(): ?int
    {

        $query = DB::connection('aurora')->table('Website User Dimension');
        if ($this->shop) {
            $query->where('Website User Website Key', $this->shop->website->source_id);
        }

        return $query->count();
    }

}
