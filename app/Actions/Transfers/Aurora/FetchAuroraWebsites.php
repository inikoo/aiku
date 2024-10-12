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
use App\Enums\Web\Webpage\WebpageSubTypeEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
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


            if ($website->state == WebsiteStateEnum::LIVE) {
                $this->saveFixedWebpageMigrationData($website, $website->storefront, 'home_logout.sys', 'loggedOut');
                $this->saveFixedWebpageMigrationData($website, $website->storefront, 'home.sys', 'loggedIn');
                $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::CONTACT)->first(), 'contact.sys');
                $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::ABOUT_US)->first(), 'about.sys');
                $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::CHECKOUT)->first(), 'checkout.sys');
                $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::BASKET)->first(), 'basket.sys');
                $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::SHIPPING)->first(), 'shipping.sys');
                $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::TERMS_AND_CONDITIONS)->first(), 'tac.sys');


                $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::LOGIN)->first(), 'login.sys');
                $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::REGISTER)->first(), 'register.sys');
                $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::CATALOGUE)->first(), 'catalogue.sys');

                $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::RETURNS)->first(), 'returns');
                $result = $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::SHOWROOM)->first(), 'showroom');
                if (!$result) {
                    $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::SHOWROOM)->first(), 'showroom.sys');
                }

                $result = $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::COOKIES_POLICY)->first(), 'cookie_policy');
                if (!$result) {
                    $result = $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::COOKIES_POLICY)->first(), 'cookies');
                }
                if (!$result) {
                    $result = $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::COOKIES_POLICY)->first(), 'cookie');
                }
                if (!$result) {
                    $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::COOKIES_POLICY)->first(), 'cookie.sys');
                }





                $result = $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::PRIVACY)->first(), 'privacy');
                if (!$result) {
                    $this->saveFixedWebpageMigrationData($website, $website->webpages()->where('sub_type', WebpageSubTypeEnum::PRIVACY)->first(), 'privacy_policy');
                }
            }
            return $website;
        }

        return null;
    }


    protected function saveFixedWebpageMigrationData(Website $website, ?Webpage $webpage, $code, $visibility = 'both'): bool
    {
        if (!$webpage) {
            return false;
        }

        $sourceData = explode(':', $website->source_id);

        $auroraModelData = DB::connection('aurora')
            ->table('Page Store Dimension')->where('Webpage Website Key', $sourceData[1])
            ->where('Webpage Code', $code)->first();


        if ($auroraModelData) {
            $webpage->updateQuietly(
                [
                    'source_id'      => $website->organisation->id.':'.$auroraModelData->{'Page Key'},
                    'migration_data' => [
                        $visibility => Arr::get($this->processAuroraWebpage($website->organisation, $auroraModelData), 'webpage.migration_data.both')
                    ]
                ]
            );

            if (!$webpage->fetched_at) {
                $webpage->updateQuietly(['fetched_at' => now()]);
            } else {
                $webpage->updateQuietly(['last_fetched_at' => now()]);
            }

            return true;
        }

        return false;
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
