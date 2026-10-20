<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\WooCommerce\Product;

use App\Models\Dropshipping\WooCommerceUser;
use Illuminate\Support\Arr;

trait WithWooProductVariations
{
    /**
     * The products listing only returns parents. What a variable product sells is its variations,
     * each with its own id and SKU, and those are only listed under the parent.
     *
     * @return array<int, array>
     */
    protected function getPublishedVariations(WooCommerceUser $wooCommerceUser, mixed $product): array
    {
        if (!is_array($product) || Arr::get($product, 'type') !== 'variable' || blank(Arr::get($product, 'variations'))) {
            return [];
        }

        $variations = [];

        for ($page = 1; $page <= 10; $page++) {
            $reply = $wooCommerceUser->getWooCommerceProductVariations((int) Arr::get($product, 'id'), [
                'per_page' => 100,
                'page'     => $page,
                'status'   => 'publish'
            ]);

            if (!is_array($reply) || !array_is_list($reply)) {
                return $variations;
            }

            foreach ($reply as $variation) {
                if (is_array($variation) && Arr::get($variation, 'id')) {
                    $variations[] = $variation;
                }
            }

            if (count($reply) < 100) {
                return $variations;
            }
        }

        return $variations;
    }

    protected function variationName(array $product, array $variation): string
    {
        $options = array_filter(Arr::pluck(Arr::get($variation, 'attributes', []), 'option'));

        return trim(Arr::get($product, 'name').' - '.implode(', ', $options), ' -');
    }
}
