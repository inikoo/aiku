<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 20:36:24 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductVariant\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\ProductVariant;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductVariantHydrateHistoricProductVariants
{
    use AsAction;
    use WithEnumStats;
    private ProductVariant $productVariant;

    public function __construct(ProductVariant $productVariant)
    {
        $this->productVariant = $productVariant;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->productVariant->id))->dontRelease()];
    }
    public function handle(ProductVariant $productVariant): void
    {

        $stats         = [
            'number_historic_product_variants' => $productVariant->historicProductVariants()->count(),
        ];

        $productVariant->stats()->update($stats);
    }

}
