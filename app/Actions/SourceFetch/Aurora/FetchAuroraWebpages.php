<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 17:56:45 Central European Summer Time, Benalmádena, Malaga Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Web\Webpage\StoreWebpage;
use App\Actions\Web\Webpage\UpdateWebpage;
use App\Models\Web\Webpage;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraWebpages extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:webpages {organisations?*} {--S|shop= : Shop slug} {--A|all= : import non online webpages as well} {--s|source_id=} {--d|db_suffix=} {--N|only_new : Fetch only new} {--d|db_suffix=} {--r|reset}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Webpage
    {
        if ($webpageData = $organisationSource->fetchWebpage($organisationSourceId)) {

            if(empty($webpageData['webpage'])) {
                return null;
            }

            //print_r($webpageData['webpage']);

            if ($webpage = Webpage::where('source_id', $webpageData['webpage']['source_id'])
                ->first()) {
                $webpage = UpdateWebpage::run(
                    webpage: $webpage,
                    modelData: $webpageData['webpage']
                );
            } else {
                data_set($modelData, 'parent_id', $webpageData['website']->storefront->id, overwrite: false);
                $webpage = StoreWebpage::make()->action(
                    parent: $webpageData['website'],
                    modelData: $webpageData['webpage'],
                );
            }

            $sourceData = explode(':', $webpage->source_id);
            DB::connection('aurora')->table('Page Store Dimension')
                ->where('Page Key', $sourceData[1])
                ->update(['aiku_id' => $webpage->id]);

            return $webpage;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        $query= DB::connection('aurora')
            ->table('Page Store Dimension')
            ->join('Website Dimension', 'Website Dimension.Website Key', '=', 'Page Store Dimension.Webpage Website Key')
            ->join('Webpage Type Dimension', 'Webpage Type Dimension.Webpage Type Key', '=', 'Page Store Dimension.Webpage Type Key')
            ->select('Page Key as source_id')
            ->where('Page Store Dimension.aiku_ignore', 'No')
            ->orderBy('source_id');


        if (!$this->fetchAll) {
            $query->where('Webpage Type Code', 'Info');
            $query->where('Website Status', 'Active');
            $query->where('Webpage State', 'Online');
        }

        if ($this->onlyNew) {
            $query->whereNull('Page Store Dimension.aiku_id');
        }
        if ($this->shop) {
            $sourceData = explode(':', $this->shop->source_id);
            $query->where('Webpage Store Key', $sourceData[1]);
        }

        return $query;

    }

    public function count(): ?int
    {
        $query= DB::connection('aurora')
            ->table('Page Store Dimension')
            ->join('Website Dimension', 'Website Dimension.Website Key', '=', 'Page Store Dimension.Webpage Website Key')
            ->join('Webpage Type Dimension', 'Webpage Type Dimension.Webpage Type Key', '=', 'Page Store Dimension.Webpage Type Key')

            ->where('aiku_ignore', 'No');
        if ($this->onlyNew) {
            $query->whereNull('Page Store Dimension.aiku_id');
        }

        if (!$this->fetchAll) {
            $query->where('Webpage Type Code', 'Info');
            $query->where('Website Status', 'Active');
            $query->where('Webpage State', 'Online');
        }

        if ($this->shop) {
            $sourceData = explode(':', $this->shop->source_id);
            $query->where('Webpage Store Key', $sourceData[1]);
        }
        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Page Store Dimension')->update(['aiku_id' => null]);
    }
}
