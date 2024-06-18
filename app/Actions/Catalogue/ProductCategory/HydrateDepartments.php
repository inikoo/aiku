<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 07 Jun 2024 00:29:36 Central European Summer Time, Abu Dhabi Airport
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory;

use App\Actions\Catalogue\ProductCategory\Hydrators\ProductCategoryHydrateFamilies;
use App\Actions\Catalogue\ProductCategory\Hydrators\ProductCategoryHydrateSales;
use App\Actions\HydrateModel;
use App\Actions\Catalogue\ProductCategory\Hydrators\DepartmentHydrateProducts;
use App\Actions\Catalogue\ProductCategory\Hydrators\DepartmentHydrateSubDepartments;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Catalogue\ProductCategory;
use Illuminate\Support\Collection;

class HydrateDepartments extends HydrateModel
{
    public string $commandSignature = 'hydrate:departments {organisations?*} {--s|slugs=} ';

    public function handle(ProductCategory $productCategory): void
    {
        DepartmentHydrateSubDepartments::run($productCategory);
        DepartmentHydrateProducts::run($productCategory);
        ProductCategoryHydrateFamilies::run($productCategory);
        //ProductCategoryHydrateSales::run($productCategory);
    }

    protected function getModel(string $slug): ProductCategory
    {
        return ProductCategory::where('slug', $slug)->where('type', ProductCategoryTypeEnum::DEPARTMENT)->first();
    }

    protected function getAllModels(): Collection
    {
        return ProductCategory::withTrashed()->where('type', ProductCategoryTypeEnum::DEPARTMENT)->get();
    }
}
