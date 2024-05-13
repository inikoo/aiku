<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 18:30:28 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory;

use App\Actions\HydrateModel;
use App\Actions\Catalogue\ProductCategory\Hydrators\ProductCategoryHydrateUniversalSearch;
use App\Models\Catalogue\ProductCategory;
use Illuminate\Support\Collection;

class UpdateProductCategoryUniversalSearch extends HydrateModel
{
    public string $commandSignature = 'product-category:search {organisations?*} {--s|slugs=}';


    public function handle(ProductCategory $productCategory): void
    {
        ProductCategoryHydrateUniversalSearch::run($productCategory);
    }


    protected function getModel(string $slug): ProductCategory
    {
        return ProductCategory::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return ProductCategory::get();
    }
}
