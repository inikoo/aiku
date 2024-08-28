<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 12:27:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Dropshipping\Shopify\Webhook\CancelledOrderWebhooksShopify;
use App\Actions\Dropshipping\Shopify\Webhook\CreateOrderWebhooksShopify;
use App\Actions\Dropshipping\Shopify\Webhook\DeleteOrderWebhooksShopify;
use App\Actions\Dropshipping\Shopify\Webhook\DeleteProductWebhooksShopify;
use App\Actions\Dropshipping\Shopify\Webhook\FulfilledOrderWebhooksShopify;
use App\Actions\Dropshipping\Shopify\Webhook\PaidOrderWebhooksShopify;
use App\Actions\Dropshipping\Shopify\Webhook\UpdateOrderWebhooksShopify;
use App\Actions\Mail\Notifications\GetSnsNotification;

Route::name('webhooks.')->group(function () {
    Route::any('sns', GetSnsNotification::class)->name('sns');
});

Route::prefix('shopify-user/{shopifyUser:id}')->name('webhooks.shopify.')->group(function () {
    Route::prefix('products')->as('products.')->group(function () {
        Route::post('delete', DeleteProductWebhooksShopify::class)->name('delete');
    });

    Route::prefix('orders')->as('orders.')->group(function () {
        Route::post('create', CreateOrderWebhooksShopify::class)->name('create');
        Route::post('updated', UpdateOrderWebhooksShopify::class)->name('updated');
        Route::post('delete', DeleteOrderWebhooksShopify::class)->name('delete');
        Route::post('paid', PaidOrderWebhooksShopify::class)->name('paid');
        Route::post('fulfilled', FulfilledOrderWebhooksShopify::class)->name('fulfilled');
        Route::post('cancelled', CancelledOrderWebhooksShopify::class)->name('cancelled');
    });
});
