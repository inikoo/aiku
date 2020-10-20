<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 20 Oct 2020 16:01:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Legacy\UserController;


Route::post(
    '/', [
           UserController::class,
           'sync'
       ]
)->name('sync_user');

Route::post(
    '/{legacy_id}', [
                      UserController::class,
                      'update'
                  ]
)->name('update_user');
