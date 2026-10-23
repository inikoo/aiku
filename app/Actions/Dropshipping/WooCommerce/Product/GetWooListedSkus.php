<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\WooCommerce\Product;

use App\Models\Dropshipping\WooCommerceUser;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class GetWooListedSkus
{
    use AsAction;
    use WithWooProductVariations;

    private const int PAGE_SIZE = 100;

    private const int MAX_PAGES = 200;

    /**
     * Every SKU in the seller's WooCommerce catalogue, keyed by its lowercased form
     * and pointing at the product or variation id a match has to be made with.
     *
     * @return array<string, string>
     */
    public function handle(WooCommerceUser $wooCommerceUser): array
    {
        $listedSkus = [];

        for ($page = 1; $page <= self::MAX_PAGES; $page++) {
            $products = $wooCommerceUser->getWooCommerceProducts([
                'per_page' => self::PAGE_SIZE,
                'page'     => $page
            ]);

            if (!is_array($products) || !array_is_list($products)) {

                return $listedSkus;
            }

            foreach ($products as $product) {
                $sku       = Arr::get($product, 'sku');
                $productId = Arr::get($product, 'id');

                if ($sku && $productId) {
                    $listedSkus[Str::lower($sku)] = (string) $productId;
                }

                foreach ($this->getPublishedVariations($wooCommerceUser, $product) as $variation) {
                    $variationSku = Arr::get($variation, 'sku');

                    if ($variationSku && $variationSku !== $sku) {
                        $listedSkus[Str::lower($variationSku)] = (string) Arr::get($variation, 'id');
                    }
                }
            }

            if (count($products) < self::PAGE_SIZE) {
                return $listedSkus;
            }
        }

        return $listedSkus;
    }
}
