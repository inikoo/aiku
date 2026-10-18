<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026 16:30:00 Central European Summer Time, Trnava, Slovakia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Bundle;

use App\Actions\Dropshipping\Portfolio\WithPortfolioSKU;
use App\Models\Catalogue\Product;
use App\Models\Dropshipping\Bundle;
use App\Models\Dropshipping\Portfolio;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * A bundle copies the trade units of its components when it is built, so a component whose own
 * trade units change afterwards leaves every bundle holding it describing the old one.
 *
 * The sku of a portfolio already linked to a listing is left alone: the listing carries it, and
 * only the customer changes what is in their store.
 */
class SyncBundlesOfComponent
{
    use AsAction;
    use WithPortfolioSKU;

    public function handle(Product $component): void
    {
        Bundle::where('bundleable_type', class_basename(Product::class))
            ->whereHas('items', fn ($query) => $query->where('item_type', class_basename(Product::class))->where('item_id', $component->id))
            ->with('bundleable')
            ->each(function (Bundle $bundle) {
                if (!$bundle->bundleable) {
                    return;
                }

                $bundleProduct = UpdateBundle::make()->writeCurrentBundleTradeUnits($bundle);

                Portfolio::where('bundle_id', $bundle->id)
                    ->whereNull('platform_product_id')
                    ->whereNull('platform_product_variant_id')
                    ->update(['sku' => $this->getSKU($bundleProduct->refresh())]);
            });
    }
}
