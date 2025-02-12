<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 15:46:00 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Transfers\Aurora\Api\ProcessAuroraCustomer;
use App\Actions\Transfers\Aurora\Api\ProcessAuroraCustomerClient;
use App\Actions\Transfers\Aurora\Api\ProcessAuroraDeliveryNote;
use App\Actions\Transfers\Aurora\Api\ProcessAuroraEmployee;
use App\Actions\Transfers\Aurora\Api\ProcessAuroraInvoice;
use App\Actions\Transfers\Aurora\Api\ProcessAuroraOrder;
use App\Actions\Transfers\Aurora\Api\ProcessAuroraProduct;
use App\Actions\Transfers\Aurora\Api\ProcessAuroraStock;
use Illuminate\Support\Facades\Route;

Route::name('bk_api.')->group(function () {
    Route::middleware(['auth:sanctum', 'ability:aurora'])->group(function () {
        Route::name('fetch.')->prefix('{organisation}')->group(function () {
            Route::post('invoice', ProcessAuroraInvoice::class)->name('invoice');
            Route::post('customer', ProcessAuroraCustomer::class)->name('customer');
            Route::post('employee', ProcessAuroraEmployee::class)->name('employee');
            Route::post('order', ProcessAuroraOrder::class)->name('order');
            Route::post('customer-client', ProcessAuroraCustomerClient::class)->name('customer_client');
            Route::post('delivery-note', ProcessAuroraDeliveryNote::class)->name('delivery_note');
            Route::post('stock', ProcessAuroraStock::class)->name('stock');
            Route::post('product', ProcessAuroraProduct::class)->name('stock');

        });
    });
});
