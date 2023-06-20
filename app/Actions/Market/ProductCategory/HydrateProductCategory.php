<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 16:57:47 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Market\ProductCategory;

use App\Actions\HydrateModel;
use App\Actions\Market\ProductCategory\Hydrators\ProductCategoryHydrateProducts;
use App\Actions\Market\ProductCategory\Hydrators\ProductCategoryHydrateSubdepartments;
use App\Models\Marketing\ProductCategory;
use Illuminate\Support\Collection;

class HydrateProductCategory extends HydrateModel
{
    public string $commandSignature = 'hydrate:department {tenants?*} {--i|id=} ';

    public function handle(ProductCategory $productCategory): void
    {
        ProductCategoryHydrateSubdepartments::run($productCategory);
        ProductCategoryHydrateProducts::run($productCategory);
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
