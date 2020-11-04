<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 20 Oct 2020 15:24:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use Illuminate\Support\Facades\Route;


Route::prefix('customers')->group(__DIR__.'/legacy/customers.php');
Route::prefix('hr')->group(__DIR__.'/legacy/hr.php');
Route::prefix('auth')->group(__DIR__.'/legacy/auth.php');
Route::prefix('stocks')->group(__DIR__.'/legacy/stocks.php');
Route::prefix('stores')->group(__DIR__.'/legacy/stores.php');
Route::prefix('products')->group(__DIR__.'/legacy/products.php');
Route::prefix('location_stock')->group(__DIR__.'/legacy/location_stock.php');
Route::prefix('orders')->group(__DIR__.'/legacy/orders.php');

