<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Feb 2023 22:01:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\HydrateModel;
use App\Actions\Catalogue\Product\Hydrators\ProductInitialiseImageID;
use App\Models\Catalogue\Product;
use Illuminate\Support\Collection;

class HydrateProduct extends HydrateModel
{
    public string $commandSignature = 'hydrate:product {organisations?*} {--i|id=} ';


    public function handle(Product $product): void
    {
        ProductInitialiseImageID::run($product);
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
