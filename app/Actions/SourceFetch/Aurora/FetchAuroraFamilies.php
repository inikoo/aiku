<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 09:32:47 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Catalogue\ProductCategory\StoreProductCategory;
use App\Actions\Catalogue\ProductCategory\UpdateProductCategory;
use App\Models\Catalogue\ProductCategory;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraFamilies extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:families {organisations?*} {--N|only_new : Fetch only new}  {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?ProductCategory
    {
        if ($familyData = $organisationSource->fetchFamily($organisationSourceId)) {
            //print_r($familyData['family']);

            if ($family = ProductCategory::where('source_family_id', $familyData['family']['source_family_id'])
                ->first()) {
                $family = UpdateProductCategory::make()->action(
                    productCategory: $family,
                    modelData: $familyData['family'],
                );
            } else {
                $family = StoreProductCategory::make()->action(
                    parent: $familyData['parent'],
                    modelData: $familyData['family']
                );
            }


            $sourceData = explode(':', $family->source_family_id);

            DB::connection('aurora')->table('Category Dimension')
                ->where('Category Key', $sourceData[1])
                ->update(['aiku_family_id' => $family->id]);

            return $family;
        }


        return null;
    }


    public function getModelsQuery(): Builder
    {
        $familySourceIDs = [];
        $query           = DB::connection('aurora')
            ->table('Store Dimension')
            ->select('Store Family Category Key');
        foreach ($query->get() as $row) {
            $familySourceIDs[] = $row->{'Store Family Category Key'};
        }


        $query = DB::connection('aurora')
            ->table('Category Dimension')
            ->select('Category Key as source_id')
            ->where('Category Branch Type', 'Head');

        if ($this->onlyNew) {
            $query->whereNull('aiku_family_id');
        }

        return $query->whereIn('Category Root Key', $familySourceIDs)
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        $familySourceIDs = [];
        $query           = DB::connection('aurora')
            ->table('Store Dimension')
            ->select('Store Family Category Key');
        foreach ($query->get() as $row) {
            $familySourceIDs[] = $row->{'Store Family Category Key'};
        }


        $query = DB::connection('aurora')
            ->table('Category Dimension')
            ->where('Category Branch Type', 'Head');
        if ($this->onlyNew) {
            $query->whereNull('aiku_family_id');
        }

        return $query->whereIn('Category Root Key', $familySourceIDs)
            ->count();
    }
}
