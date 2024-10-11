<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 17:56:45 Central European Summer Time, Benalmádena, Malaga Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Web\Website\LaunchWebsite;
use App\Actions\Web\Website\StoreWebsite;
use App\Actions\Web\Website\UpdateWebsite;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use App\Transfers\Aurora\WithAuroraParsers;
use App\Transfers\Aurora\WithAuroraProcessWebpage;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FetchAuroraWebsites extends FetchAuroraAction
{
    use WithAuroraParsers;
    use WithAuroraProcessWebpage;


    public string $commandSignature = 'fetch:websites {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Website
    {
        if ($websiteData = $organisationSource->fetchWebsite($organisationSourceId)) {
            if ($website = Website::where('source_id', $websiteData['website']['source_id'])
                ->first()) {
                $website = UpdateWebsite::make()->action(
                    website: $website,
                    modelData: $websiteData['website'],
                    hydratorsDelay: 60,
                    strict: false,
                    audit: false
                );
            } else {
                $website = StoreWebsite::make()->action(
                    shop: $websiteData['shop'],
                    modelData: $websiteData['website'],
                    hydratorsDelay: 60,
                    strict: false
                );


                if ($websiteData['launch']) {
                    LaunchWebsite::run(website: $website);
                }
            }

            // Get Storefront data
            //            $auroraModelData = DB::connection('aurora')->table('Page Store Dimension')->where()->where('Webpage Code', 'home_logout.sys')->first();
            //            $website->storefront->updateQuietly(['migration_data' => ['loggedOut' => Arr::get($this->processAuroraWebpage($website->organisation, $auroraModelData), 'webpage.migration_data.both')]]);
            //
            //
            //            $auroraModelData = DB::connection('aurora')->table('Page Store Dimension')->where('Webpage Code', 'home.sys')->first();
            //            $website->storefront->updateQuietly(['migration_data' => ['loggedIn' => Arr::get($this->processAuroraWebpage($website->organisation, $auroraModelData), 'webpage.migration_data.both')]]);
            //
            //
            //            $auroraModelData = DB::connection('aurora')->table('Page Store Dimension')->where('Webpage Code', 'contact.sys')->first();
            //            $website->storefront->updateQuietly(['migration_data' => ['both' => Arr::get($this->processAuroraWebpage($website->organisation, $auroraModelData), 'webpage.migration_data.both')]]);


            $this->saveFixedWebpageMigrationData($website, $website->storefront, 'home_logout.sys', 'loggedOut');
            $this->saveFixedWebpageMigrationData($website, $website->storefront, 'home.sys', 'loggedIn');
            $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('code', 'contact')->first(), 'contact.sys');



            return $website;
        }

        return null;
    }


    protected function saveFixedWebpageMigrationData(Website $website, ?Webpage $webpage, $code, $visibility = 'both'): void
    {

        if (!$webpage) {
            return;
        }

        $sourceData = explode(':', $website->source_id);

        $auroraModelData = DB::connection('aurora')
            ->table('Page Store Dimension')->where('Webpage Website Key', $sourceData[1])
            ->where('Webpage Code', $code)->first();

        $webpage->updateQuietly(
            [
                'migration_data' => [
                    $visibility => Arr::get($this->processAuroraWebpage($website->organisation, $auroraModelData), 'webpage.migration_data.both')
                ]
            ]
        );
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Website Dimension')
            ->select('Website Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Website Dimension')->count();
    }
}
