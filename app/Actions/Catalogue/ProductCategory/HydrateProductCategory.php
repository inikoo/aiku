<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 16:57:47 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory;

use App\Actions\Catalogue\ProductCategory\Hydrators\FamilyHydrateProducts;
use App\Actions\Catalogue\ProductCategory\Hydrators\ProductCategoryHydrateFamilies;
use App\Actions\Catalogue\ProductCategory\Hydrators\ProductCategoryHydrateSales;
use App\Actions\Catalogue\ProductCategory\Hydrators\SubDepartmentHydrateSubDepartments;
use App\Actions\HydrateModel;
use App\Actions\Catalogue\ProductCategory\Hydrators\DepartmentHydrateProducts;
use App\Actions\Catalogue\ProductCategory\Hydrators\DepartmentHydrateSubDepartments;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Catalogue\ProductCategory;
use Illuminate\Support\Collection;

class HydrateProductCategory extends HydrateModel
{
    public string $commandSignature = 'product-category:hydrate {organisations?*} {--s|slugs=} ';

    public function handle(ProductCategory $productCategory): void
    {
        if ($productCategory->type == ProductCategoryTypeEnum::DEPARTMENT) {
            DepartmentHydrateSubDepartments::run($productCategory);
            DepartmentHydrateProducts::run($productCategory);
            ProductCategoryHydrateFamilies::run($productCategory);
        } elseif ($productCategory->type == ProductCategoryTypeEnum::SUB_DEPARTMENT) {
            SubDepartmentHydrateSubDepartments::run($productCategory);
            ProductCategoryHydrateFamilies::run($productCategory);
        } else {
            FamilyHydrateProducts::run($productCategory);
        }
        //ProductCategoryHydrateSales::run($productCategory);
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
