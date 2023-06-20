<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 08:14:07 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Market\ProductCategory\StoreProductCategory;
use App\Actions\Market\ProductCategory\UpdateProductCategory;
use App\Models\Marketing\ProductCategory;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchDepartments extends FetchAction
{
    public string $commandSignature = 'fetch:departments {tenants?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?ProductCategory
    {
        if ($productCategoryData = $tenantSource->fetchDepartment($tenantSourceId)) {
            if ($productCategory = ProductCategory::where('source_department_id', $productCategoryData['department']['source_department_id'])
                ->first()) {
                $productCategory = UpdateProductCategory::run(
                    productCategory: $productCategory,
                    modelData: $productCategoryData['department'],
                );
            } else {
                $productCategory = StoreProductCategory::run(
                    parent: $productCategoryData['shop'],
                    modelData: $productCategoryData['department']
                );
            }

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
