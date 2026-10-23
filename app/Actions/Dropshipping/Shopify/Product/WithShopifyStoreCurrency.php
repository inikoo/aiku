<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 18:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify\Product;

use App\Actions\Helpers\CurrencyExchange\GetCurrencyExchange;
use App\Models\Dropshipping\ShopifyUser;
use App\Models\Helpers\Currency;
use Illuminate\Support\Arr;

trait WithShopifyStoreCurrency
{
    /**
     * Shopify shows a price in the store's own currency. A store in the shop's currency, or one
     * whose currency CheckShopifyChannel has not read yet, gets the price as it is.
     *
     * @return array{0: mixed, 1: string|null} the price for the store, or null and why not
     */
    protected function priceInShopifyStoreCurrency(ShopifyUser $shopifyUser, Currency $shopCurrency, $price): array
    {
        $storeCurrencyCode = Arr::get($shopifyUser->data, 'shop.currencyCode');

        if ($price === null || !$storeCurrencyCode || $storeCurrencyCode === $shopCurrency->code) {
            return [$price, null];
        }

        $storeCurrency = Currency::where('code', $storeCurrencyCode)->first();

        if (!$storeCurrency) {
            return [null, "The Shopify store sells in $storeCurrencyCode, a currency we do not know, the price was not sent"];
        }

        $exchange = GetCurrencyExchange::run($shopCurrency, $storeCurrency);

        if (!$exchange) {
            return [null, "The Shopify store sells in $storeCurrencyCode and the {$shopCurrency->code} to $storeCurrencyCode exchange rate is not available, the price was not sent"];
        }

        return [number_format((float) $price * $exchange, 2, '.', ''), null];
    }
}
