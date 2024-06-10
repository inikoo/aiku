<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 09:33:59 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\Catalogue\Product\Hydrators\ProductHydrateHistoricAssets;
use App\Actions\Catalogue\Product\Hydrators\ProductHydrateProductVariants;
use App\Actions\HydrateModel;
use App\Models\Catalogue\Product;
use Illuminate\Support\Collection;

class HydrateProduct extends HydrateModel
{
    public string $commandSignature = 'product:hydrate {organisations?*} {--s|slugs=} ';


    public function handle(Product $product): void
    {
        ProductHydrateHistoricAssets::run($product);
        ProductHydrateProductVariants::run($product);


    }


    protected function getModel(string $slug): Product
    {
        return Product::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Product::withTrashed()->get();
    }
}
