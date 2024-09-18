<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 29 Aug 2024 00:18:41 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Dispatching\Picking\AssignPackerToPicking;
use App\Actions\Dispatching\Picking\AssignPickerToPicking;
use App\Actions\Dispatching\Picking\UpdatePickingStateToDone;
use App\Actions\Dispatching\Picking\UpdatePickingStateToPacking;
use App\Actions\Dispatching\Picking\UpdatePickingStateToPicked;
use App\Actions\Dispatching\Picking\UpdatePickingStateToPicking;
use App\Actions\Dispatching\Picking\UpdatePickingStateToQueried;
use App\Actions\Dispatching\Picking\UpdatePickingStateToWaiting;
use App\Actions\Ordering\Order\PayOrder;
use App\Actions\Ordering\Order\SendOrderToWarehouse;
use App\Actions\Ordering\Order\UpdateOrder;
use App\Actions\Ordering\Order\UpdateOrderStateToSubmitted;
use App\Actions\Ordering\Order\UpdateStateToCreatingOrder;
use App\Actions\Ordering\Order\UpdateStateToDispatchedOrder;
use App\Actions\Ordering\Order\UpdateStateToFinalizedOrder;
use App\Actions\Ordering\Order\UpdateStateToHandlingOrder;
use App\Actions\Ordering\Order\UpdateStateToPackedOrder;
use App\Actions\Ordering\Transaction\DeleteTransaction;
use App\Actions\Ordering\Transaction\StoreTransaction;
use App\Actions\Ordering\Transaction\UpdateTransaction;
use Illuminate\Support\Facades\Route;

Route::name('order.')->prefix('order/{order:id}')->group(function () {
    Route::patch('update', UpdateOrder::class)->name('update');
    Route::post('customer/{customer:id}/payment/{paymentAccount:id}', PayOrder::class)->name('payment.store')->withoutScopedBindings();


    Route::name('transaction.')->prefix('transaction')->group(function () {
        Route::patch('{transaction:id}', UpdateTransaction::class)->name('update')->withoutScopedBindings();
        Route::delete('{transaction:id}', DeleteTransaction::class)->name('delete')->withoutScopedBindings();
        Route::post('{historicAsset:id}', StoreTransaction::class)->name('store')->withoutScopedBindings();
    });

    Route::name('state.')->prefix('state')->group(function () {
        Route::patch('creating', UpdateStateToCreatingOrder::class)->name('creating');
        Route::patch('submitted', UpdateOrderStateToSubmitted::class)->name('submitted');
        Route::patch('in-warehouse', SendOrderToWarehouse::class)->name('in-warehouse');
        Route::patch('handling', UpdateStateToHandlingOrder::class)->name('handling');
        Route::patch('packed', UpdateStateToPackedOrder::class)->name('packed');
        Route::patch('finalized', UpdateStateToFinalizedOrder::class)->name('finalized');
        Route::patch('dispatched', UpdateStateToDispatchedOrder::class)->name('dispatched');
    });
});

Route::name('delivery-note.')->prefix('delivery-note/{deliveryNote:id}')->group(function () {
});

Route::name('picking.')->prefix('picking/{picking:id}')->group(function () {

    Route::name('assign.')->prefix('assign')->group(function () {
        Route::patch('picker', AssignPickerToPicking::class)->name('picker');
        Route::patch('packer', AssignPackerToPicking::class)->name('packer');
    });

    Route::name('state.')->prefix('state')->group(function () {
        Route::patch('picking', UpdatePickingStateToPicking::class)->name('picking');
        Route::patch('queried', UpdatePickingStateToQueried::class)->name('queried');
        Route::patch('waiting', UpdatePickingStateToWaiting::class)->name('waiting');
        Route::patch('picked', UpdatePickingStateToPicked::class)->name('picked');
        Route::patch('packed', UpdatePickingStateToPacking::class)->name('packing');
        Route::patch('done', UpdatePickingStateToDone::class)->name('done');
    });
});
