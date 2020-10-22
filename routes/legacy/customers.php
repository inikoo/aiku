<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 20 Oct 2020 15:25:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Legacy\CustomerController;
use App\Http\Controllers\Legacy\CustomerClientController;


Route::post(
    '/', [
           CustomerController::class,
           'sync'
       ]
)->name('sync_customer');

Route::post(
    '/{legacy_id}', [
                      CustomerController::class,
                      'update'
                  ]
)->name('update_customer');

Route::post(
    '/{legacy_id}/basket', [
                      CustomerController::class,
                      'update_basket'
                  ]
)->name('update_customer_basket');


Route::post(
    '/{legacy_customer_id}/portfolio/{legacy_product_id}', [
                                                             CustomerController::class,
                                                             'sync_portfolio'
                                                         ]
)->name('sync_portfolio');


Route::post(
    '/customer_client', [
                          CustomerClientController::class,
                          'sync'
                      ]
)->name('sync_customer_client');


Route::post(
    '/customer_client/{legacy_id}', [
                                      CustomerClientController::class,
                                      'update'
                                  ]
)->name('update_customer_client');

Route::post(
    '/customer_client/{legacy_id}/basket', [
                                      CustomerClientController::class,
                                      'update_basket'
                                  ]
)->name('update_customer_client_basket');

