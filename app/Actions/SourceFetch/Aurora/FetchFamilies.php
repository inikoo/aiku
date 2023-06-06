<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 09:32:47 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Marketing\ProductCategory\StoreProductCategory;
use App\Actions\Marketing\ProductCategory\UpdateProductCategory;
use App\Models\Marketing\ProductCategory;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchFamilies extends FetchAction
{
    public string $commandSignature = 'fetch:families {tenants?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?ProductCategory
    {
        if ($familyData = $tenantSource->fetchFamily($tenantSourceId)) {
            if ($family = ProductCategory::where('source_family_id', $familyData['family']['source_family_id'])
                ->first()) {
                $family = UpdateProductCategory::run(
                    productCategory:    $family,
                    modelData: $familyData['family'],
                );
            } else {
                $family = StoreProductCategory::run(
                    parent:    $familyData['parent'],
                    modelData: $familyData['family']
                );
            }

            return $family;
        }


        return null;
    }


    public function getModelsQuery(): Builder
    {

        $familySourceIDs=[];
        $query          =DB::connection('aurora')
            ->table('Store Dimension')
            ->select('Store Family Category Key');
        foreach($query->get() as $row) {
            $familySourceIDs[]=$row->{'Store Family Category Key'};
        }



        return DB::connection('aurora')
            ->table('Category Dimension')
            ->select('Category Key as source_id')
            ->where('Category Branch Type', 'Head')
            ->whereIn('Category Root Key', $familySourceIDs)
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        $familySourceIDs=[];
        $query          =DB::connection('aurora')
            ->table('Store Dimension')
            ->select('Store Family Category Key');
        foreach($query->get() as $row) {
            $familySourceIDs[]=$row->{'Store Family Category Key'};
        }



        return DB::connection('aurora')
            ->table('Category Dimension')
            ->where('Category Branch Type', 'Head')
            ->whereIn('Category Root Key', $familySourceIDs)
            ->count();
    }
}
