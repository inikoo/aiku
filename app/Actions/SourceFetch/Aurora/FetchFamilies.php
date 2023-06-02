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
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchFamilies
{
    use AsAction;


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?ProductCategory
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
}
