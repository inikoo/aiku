<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 12:27:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Dropshipping\Shopify\DeleteProductFromShopify;
use App\Actions\Mail\Notifications\GetSnsNotification;
use App\Stubs\UIDummies\CreateDummy;

Route::name('webhooks.')->group(function () {
    Route::any('sns', GetSnsNotification::class)->name('sns');
});

Route::prefix('shopify')->name('webhooks.shopify.')->group(function () {
    Route::prefix('products')->as('products.')->group(function () {
        Route::post('deleted', [DeleteProductFromShopify::class, 'inWebhook'])->name('deleted');
    });

    Route::prefix('orders')->as('orders.')->group(function () {
        Route::post('created', CreateDummy::class)->name('create');
        Route::post('updated', CreateDummy::class)->name('updated');
        Route::post('delete', CreateDummy::class)->name('delete');
        Route::post('paid', CreateDummy::class)->name('paid');
        Route::post('edited', CreateDummy::class)->name('edited');
        Route::post('fulfilled', CreateDummy::class)->name('fulfilled');
        Route::post('cancelled', CreateDummy::class)->name('cancelled');
    });
});
