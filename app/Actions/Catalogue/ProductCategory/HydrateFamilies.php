<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 07 Jun 2024 00:42:11 Central European Summer Time, Abu Dhabi Airport
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory;

use App\Actions\Catalogue\ProductCategory\Hydrators\FamilyHydrateProducts;
use App\Actions\Catalogue\ProductCategory\Hydrators\ProductCategoryHydrateSales;
use App\Actions\HydrateModel;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Catalogue\ProductCategory;
use Illuminate\Support\Collection;

class HydrateFamilies extends HydrateModel
{
    public string $commandSignature = 'hydrate:families {organisations?*} {--s|slugs=} ';

    public function handle(ProductCategory $productCategory): void
    {
        FamilyHydrateProducts::run($productCategory);
        //ProductCategoryHydrateSales::run($productCategory);
    }

    protected function getModel(string $slug): ProductCategory
    {
        return ProductCategory::where('slug', $slug)->where('type', ProductCategoryTypeEnum::FAMILY)->first();
    }

    protected function getAllModels(): Collection
    {
        return ProductCategory::withTrashed()->where('type', ProductCategoryTypeEnum::FAMILY)->get();
    }
}
