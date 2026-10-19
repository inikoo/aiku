<?php

namespace App\Actions\Catalogue\Product\Hydrators;

use App\Enums\Catalogue\Product\ProductStatusEnum;
use App\Models\Accounting\InvoiceTransaction;
use App\Models\Catalogue\Product;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductHydrateFirstAvailableAt
{
    use AsAction;

    /**
     * Stamps, once and for good, the moment a product can first be ordered on the website:
     * for sale, in stock and on a live webpage. The NEW IN badge counts from it.
     * A product already sold before keeps the date of its first sale, so it is not shown as new.
     */
    public function handle(Product $product): Product
    {
        if ($product->first_available_at) {
            return $product;
        }

        if (!$product->is_for_sale || !$product->has_live_webpage || $product->status !== ProductStatusEnum::FOR_SALE) {
            return $product;
        }

        $product->update(['first_available_at' => self::firstInvoicedAt($product) ?? now()]);

        return $product;
    }

    public static function firstInvoicedAt(Product $product): ?Carbon
    {
        if (!$product->asset_id) {
            return null;
        }

        $firstInvoicedAt = InvoiceTransaction::where('asset_id', $product->asset_id)->min('date');

        return $firstInvoicedAt ? Carbon::parse($firstInvoicedAt) : null;
    }
}
