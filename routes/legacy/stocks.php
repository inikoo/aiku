<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 20 Oct 2020 16:42:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Legacy\StockController;


Route::post(
    '/', [
           StockController::class,
           'sync'
       ]
)->name('sync_stock');

Route::post(
    '/{legacy_id}', [
                      StockController::class,
                      'update'
                  ]
)->name('update_stock');
