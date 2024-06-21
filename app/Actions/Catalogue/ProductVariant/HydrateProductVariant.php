<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 09:33:59 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductVariant;

use App\Actions\Catalogue\ProductVariant\Hydrators\ProductVariantHydrateHistoricProductVariants;
use App\Actions\HydrateModel;
use App\Models\Catalogue\ProductVariant;
use Illuminate\Support\Collection;

class HydrateProductVariant extends HydrateModel
{
    public string $commandSignature = 'hydrate:product-variants {organisations?*} {--slugs=} ';


    public function handle(ProductVariant $productVariant): void
    {
        ProductVariantHydrateHistoricProductVariants::run($productVariant);

    }

    protected function getModel(string $slug): ProductVariant
    {
        return ProductVariant::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return ProductVariant::withTrashed()->get();
    }
}
