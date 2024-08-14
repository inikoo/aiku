<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 15:46:00 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Transfers\Aurora\FetchAuroraCustomers;
use App\Actions\Transfers\Aurora\FetchAuroraInvoices;
use Illuminate\Support\Facades\Route;

Route::name('bk_api.')->group(function () {
    Route::middleware(['auth:sanctum', 'ability:aurora'])->group(function () {
        Route::prefix('{organisation}')->group(function () {
            Route::post('invoice', FetchAuroraInvoices::class)->name('invoice.fetch');
            Route::post('customer', FetchAuroraCustomers::class)->name('invoice.fetch');
        });
    });
});
