<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 15:46:00 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Dropshipping\Api\IndexCustomerPortfolio;
use App\Actions\Dropshipping\Api\IndexCustomers;
use App\Actions\Dropshipping\Api\IndexProducts;
use App\Actions\Dropshipping\Api\IndexShops;
use App\Actions\Dropshipping\Api\IndexProductCustomers;
use App\Actions\Dropshipping\Api\ShowCustomer;
use App\Actions\Dropshipping\Api\ShowProduct;
use App\Actions\Dropshipping\Api\ShowShop;
use App\Actions\Dropshipping\ConnectToDroppings;
use Illuminate\Support\Facades\Route;

Route::name('ds_api.')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('shops')->name('shops.')->group(function () {
            Route::get('', IndexShops::class)->name('index');

            Route::prefix('{shop:id}')->name('show')->group(function () {
                Route::get('', ShowShop::class);
                Route::get('customers', IndexCustomers::class)->name('.customers.index');
                Route::get('products', IndexProducts::class)->name('.products.index');

            });
        });

        Route::get('customers/{customer:id}', ShowCustomer::class)->name('customers.show');
        Route::get('customers/{customer:id}/products', IndexCustomerPortfolio::class)->name('customers.show.products.index');

        Route::get('products/{product:id}', ShowProduct::class)->name('products.show');
        Route::get('products/{product:id}/customers', IndexProductCustomers::class)->name('products.show.customers.index');

    });


    Route::post('connect', ConnectToDroppings::class)->name('connect');
});
