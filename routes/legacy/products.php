<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 20 Oct 2020 23:41:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Legacy\ProductController;


Route::post(
    '/', [
           ProductController::class,
           'sync'
       ]
)->name('sync_product');

Route::post(
    '/{legacy_id}', [
                      ProductController::class,
                      'update'
                  ]
)->name('update_product');
