<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 17:56:45 Central European Summer Time, Benalmádena, Malaga Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;


use App\Actions\Marketing\Website\StoreWebsite;
use App\Actions\Marketing\Website\UpdateWebsite;
use App\Models\Marketing\Website;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchWebsites extends FetchAction
{


    public string $commandSignature = 'fetch:websites {tenants?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Website
    {
        if ($websiteData = $tenantSource->fetchWebsite($tenantSourceId)) {
            if ($website = Website::where('source_id', $websiteData['website']['source_id'])
                ->first()) {
                $website = UpdateWebsite::run(
                    website:   $website,
                    modelData: $websiteData['website']
                );
            } else {
                $website = StoreWebsite::run(
                    shop:      $websiteData['shop'],
                    modelData: $websiteData['website'],
                );
                usleep(1000000);

            }

            return $website;
        }

        return null;
    }

    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Website Dimension')
            ->select('Website Key as source_id')
            ->orderBy('source_id');
    }

    function count(): ?int
    {
        return DB::connection('aurora')->table('Website Dimension')->count();
    }

}
