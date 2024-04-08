<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 18:29:16 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Product;

use App\Actions\HydrateModel;
use App\Actions\Market\Product\Hydrators\ProductHydrateUniversalSearch;
use App\Models\Market\Product;
use Illuminate\Support\Collection;

class UpdateProductUniversalSearch extends HydrateModel
{
    public string $commandSignature = 'product:search {organisations?*} {--s|slugs=}';


    public function handle(Product $product): void
    {
        ProductHydrateUniversalSearch::run($product);
    }


    protected function getModel(string $slug): Product
    {
        return Product::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Product::get();
    }
}
