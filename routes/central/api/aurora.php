<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 17 Nov 2022 12:20:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Actions\SourceFetch\Aurora\FetchCustomers;
use App\Actions\SourceFetch\Aurora\FetchEmployees;
use App\Actions\SourceFetch\Aurora\FetchGuests;
use App\Actions\SourceFetch\Aurora\FetchLocations;
use App\Actions\SourceFetch\Aurora\FetchOrders;
use App\Actions\SourceFetch\Aurora\FetchShops;
use App\Actions\SourceFetch\Aurora\FetchStocks;
use App\Actions\SourceFetch\Aurora\FetchWarehouseAreas;
use App\Actions\SourceFetch\Aurora\FetchWarehouses;
use Illuminate\Support\Facades\Route;


Route::post('/shop', FetchShops::class)->name('shops');
Route::post('/customer', FetchCustomers::class)->name('customers');
Route::post('/order', FetchOrders::class)->name('orders');

Route::post('/warehouse', FetchWarehouses::class)->name('warehouses');
Route::post('/warehouse_area', FetchWarehouseAreas::class)->name('warehouse_areas');
Route::post('/location', FetchLocations::class)->name('locations');
Route::post('/stock', FetchStocks::class)->name('stocks');

Route::post('/employee', FetchEmployees::class)->name('employee');
Route::post('/guest', FetchGuests::class)->name('guest');
