<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 16:57:47 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory;

use App\Actions\HydrateModel;
use App\Actions\Catalogue\ProductCategory\Hydrators\ProductCategoryHydrateProducts;
use App\Actions\Catalogue\ProductCategory\Hydrators\ProductCategoryHydrateSubdepartments;
use App\Models\Catalogue\ProductCategory;
use Illuminate\Support\Collection;

class HydrateProductCategory extends HydrateModel
{
    public string $commandSignature = 'hydrate:department {organisations?*} {--i|id=} ';

    public function handle(ProductCategory $productCategory): void
    {
        ProductCategoryHydrateSubdepartments::run($productCategory);
        ProductCategoryHydrateProducts::run($productCategory);
    }

    protected function getModel(string $slug): ProductCategory
    {
        return ProductCategory::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return ProductCategory::withTrashed()->get();
    }
}
