<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 08:14:07 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Catalogue\ProductCategory\StoreProductCategory;
use App\Actions\Catalogue\ProductCategory\UpdateProductCategory;
use App\Models\Catalogue\ProductCategory;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraDepartments extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:departments {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?ProductCategory
    {
        if ($productCategoryData = $organisationSource->fetchDepartment($organisationSourceId)) {
            if ($productCategory = ProductCategory::where('source_department_id', $productCategoryData['department']['source_department_id'])
                ->first()) {
                $productCategory = UpdateProductCategory::make()->action(
                    productCategory: $productCategory,
                    modelData: $productCategoryData['department'],
                );
            } else {
                $productCategory = StoreProductCategory::make()->action(
                    parent: $productCategoryData['shop'],
                    modelData: $productCategoryData['department']
                );
            }

            $sourceData = explode(':', $productCategory->source_department_id);

            DB::connection('aurora')->table('Category Dimension')
                ->where('Category Key', $sourceData[1])
                ->update(['aiku_department_id' => $productCategory->id]);

            return $productCategory;
        }



        return null;
    }


    public function getModelsQuery(): Builder
    {

        $departmentSourceIDs=[];
        $query              =DB::connection('aurora')
            ->table('Store Dimension')
            ->select('Store Department Category Key');
        foreach($query->get() as $row) {
            $departmentSourceIDs[]=$row->{'Store Department Category Key'};
        }



        return DB::connection('aurora')
            ->table('Category Dimension')
            ->select('Category Key as source_id')
            ->where('Category Branch Type', 'Head')
            ->whereIn('Category Root Key', $departmentSourceIDs)
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        $departmentSourceIDs=[];
        $query              =DB::connection('aurora')
            ->table('Store Dimension')
            ->select('Store Department Category Key');
        foreach($query->get() as $row) {
            $departmentSourceIDs[]=$row->{'Store Department Category Key'};
        }



        return DB::connection('aurora')
            ->table('Category Dimension')
            ->where('Category Branch Type', 'Head')
            ->whereIn('Category Root Key', $departmentSourceIDs)
            ->count();
    }


}
