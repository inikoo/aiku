<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 20 Oct 2020 16:04:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Legacy\StoreController;


Route::post(
    '/', [
           StoreController::class,
           'sync'
       ]
)->name('sync_store');

Route::post(
    '/{legacy_id}', [
                      StoreController::class,
                      'update'
                  ]
)->name('update_store');
