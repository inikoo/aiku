<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 08:14:07 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Marketing\ProductCategory\StoreProductCategory;
use App\Actions\Marketing\ProductCategory\UpdateProductCategory;
use App\Models\Marketing\ProductCategory;
use App\Services\Tenant\SourceTenantService;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchDepartments
{
    use AsAction;


    public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?ProductCategory
    {
        if ($productCategoryData = $tenantSource->fetchDepartment($tenantSourceId)) {
            if ($productCategory = ProductCategory::where('source_department_id', $productCategoryData['department']['source_department_id'])
                ->first()) {
                $productCategory = UpdateProductCategory::run(
                    productCategory: $productCategory,
                    modelData:  $productCategoryData['department'],
                );
            } else {
                $productCategory = StoreProductCategory::run(
                    parent:      $productCategoryData['shop'],
                    modelData: $productCategoryData['department']
                );
            }

            return $productCategory;
        }


        return null;
    }
}
