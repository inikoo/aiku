<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 15:46:00 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Dropshipping\Api\IndexDropshippingCustomers;
use App\Actions\Dropshipping\Api\IndexDropshippingProducts;
use App\Actions\Dropshipping\Api\IndexDropshippingShops;
use App\Actions\Dropshipping\Api\ShowDropshippingCustomer;
use App\Actions\Dropshipping\Api\ShowDropshippingProduct;
use App\Actions\Dropshipping\Api\ShowDropshippingShop;
use App\Actions\Dropshipping\ConnectToDroppings;
use Illuminate\Support\Facades\Route;

Route::name('ds_api.')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('shops')->name('shops.')->group(function () {
            Route::get('', IndexDropshippingShops::class)->name('index');

            Route::prefix('{shop:id}')->name('show')->group(function () {
                Route::get('', ShowDropshippingShop::class);
                Route::get('customers', IndexDropshippingCustomers::class)->name('.customers.index');
                Route::get('products', IndexDropshippingProducts::class)->name('.products.index');

            });
        });

        Route::get('customers/{customer:id}', ShowDropshippingCustomer::class)->name('customers.show');
        Route::get('products/{product:id}', ShowDropshippingProduct::class)->name('products.show');

    });


    Route::post('connect', ConnectToDroppings::class)->name('connect');
});
