<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Wed, 04 Nov 2020 11:59:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use App\Http\Controllers\Legacy\OrderController;
use Illuminate\Support\Facades\Route;


Route::post(
    '/', [
           OrderController::class,
           'sync'
       ]
)->name('sync_order');

Route::post(
    '/{legacy_id}', [
                      OrderController::class,
                      'update'
                  ]
)->name('update_order');




