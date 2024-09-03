<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 12:27:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Dropshipping\Shopify\Webhook\CreateFulfilmentWebhooksShopify;
use App\Actions\Dropshipping\Shopify\Webhook\DeleteProductWebhooksShopify;
use App\Actions\Mail\Notifications\GetSnsNotification;

Route::name('webhooks.')->group(function () {
    Route::any('sns', GetSnsNotification::class)->name('sns');
});

Route::prefix('shopify-user/{shopifyUser:id}')->name('webhooks.shopify.')->group(function () {
    Route::prefix('products')->as('products.')->group(function () {
        Route::post('delete', DeleteProductWebhooksShopify::class)->name('delete');
    });

    Route::prefix('fulfillments')->as('fulfillments.')->group(function () {
        Route::post('create', CreateFulfilmentWebhooksShopify::class)->name('create');
    });
});
