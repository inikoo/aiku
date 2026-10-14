<?php

namespace App\Actions\Catalogue\Product\Hydrators;

use App\Enums\Catalogue\Product\ProductStatusEnum;
use App\Models\Catalogue\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductHydrateFirstAvailableAt
{
    use AsAction;

    /**
     * Stamps, once and for good, the moment a product can first be ordered on the website:
     * for sale, in stock and on a live webpage. The NEW IN badge counts from it.
     */
    public function handle(Product $product): Product
    {
        if ($product->first_available_at) {
            return $product;
        }

        if (!$product->is_for_sale || !$product->has_live_webpage || $product->status !== ProductStatusEnum::FOR_SALE) {
            return $product;
        }

        $product->update(['first_available_at' => now()]);

        return $product;
    }
}
