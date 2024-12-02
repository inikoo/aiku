<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Aug 2024 22:13:51 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory\Search;

use App\Actions\HydrateModel;
use App\Models\Catalogue\ProductCategory;
use Illuminate\Support\Collection;

class ReindexProductCategorySearch extends HydrateModel
{
    public string $commandSignature = 'product_category:search {organisations?*} {--s|slugs=}';


    public function handle(ProductCategory $productCategory): void
    {
        ProductCategoryRecordSearch::run($productCategory);
    }

    protected function getModel(string $slug): ProductCategory
    {
        return ProductCategory::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return ProductCategory::withTrashed()->get();
    }
}
