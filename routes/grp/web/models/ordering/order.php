<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 29 Aug 2024 00:18:41 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Ordering\Transaction\StoreTransaction;
use Illuminate\Support\Facades\Route;

Route::name('order.')->prefix('order/{order:id}')->group(function () {
    Route::name('transaction.')->prefix('transaction')->group(function () {
        Route::post('{historicAsset:id}', StoreTransaction::class)->name('store')->withoutScopedBindings();
    });
});
