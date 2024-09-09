<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 29 Aug 2024 00:18:41 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Ordering\Order\UpdateOrder;
use App\Actions\Ordering\Order\UpdateOrderStateToInWarehouse;
use App\Actions\Ordering\Order\UpdateOrderStateToSubmitted;
use App\Actions\Ordering\Order\UpdateStateToCreatingOrder;
use App\Actions\Ordering\Order\UpdateStateToDispatchedOrder;
use App\Actions\Ordering\Order\UpdateStateToFinalizedOrder;
use App\Actions\Ordering\Order\UpdateStateToHandlingOrder;
use App\Actions\Ordering\Order\UpdateStateToPackedOrder;
use App\Actions\Ordering\Transaction\StoreTransaction;
use Illuminate\Support\Facades\Route;

Route::name('order.')->prefix('order/{order:id}')->group(function () {
    Route::patch('update', UpdateOrder::class)->name('update');
    Route::name('transaction.')->prefix('transaction')->group(function () {
        Route::post('{historicAsset:id}', StoreTransaction::class)->name('store')->withoutScopedBindings();
    });
    Route::name('state.')->prefix('state')->group(function () {
        Route::patch('creating', UpdateStateToCreatingOrder::class)->name('creating');
        Route::patch('submitted', UpdateOrderStateToSubmitted::class)->name('submitted');
        Route::patch('in-warehouse', UpdateOrderStateToInWarehouse::class)->name('in-warehouse');
        Route::patch('handling', UpdateStateToHandlingOrder::class)->name('handling');
        Route::patch('packed', UpdateStateToPackedOrder::class)->name('packed');
        Route::patch('finalized', UpdateStateToFinalizedOrder::class)->name('finalized');
        Route::patch('dispatched', UpdateStateToDispatchedOrder::class)->name('dispatched');
    });
});
