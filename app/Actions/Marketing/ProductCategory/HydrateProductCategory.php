<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 15 Feb 2022 22:35:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Marketing\ProductCategory;

use App\Actions\HydrateModel;
use App\Actions\Marketing\ProductCategory\Hydrators\ProductCategoryHydrateSubdepartments;
use App\Actions\Marketing\ProductCategory\Hydrators\DepartmentHydrateProducts;
use App\Models\Marketing\ProductCategory;
use Illuminate\Support\Collection;

class HydrateProductCategory extends HydrateModel
{
    public string $commandSignature = 'hydrate:department {tenants?*} {--i|id=} ';

    public function handle(ProductCategory $productCategory): void
    {
        ProductCategoryHydrateSubdepartments::run($productCategory);
        DepartmentHydrateProducts::run($productCategory);
    }

    protected function getModel(int $id): ProductCategory
    {
        return ProductCategory::find($id);
    }

    protected function getAllModels(): Collection
    {
        return ProductCategory::withTrashed()->get();
    }
}
