<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 10 Oct 2022 10:38:10 Central European Summer Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Actions\SourceFetch\Aurora\FetchCustomers;
use App\Actions\SourceFetch\Aurora\FetchEmployees;
use App\Actions\SourceFetch\Aurora\FetchLocations;
use App\Actions\SourceFetch\Aurora\FetchOrders;
use App\Actions\SourceFetch\Aurora\FetchShops;
use App\Actions\SourceFetch\Aurora\FetchStocks;
use App\Actions\SourceFetch\Aurora\FetchWarehouseAreas;
use App\Actions\SourceFetch\Aurora\FetchWarehouses;
use Illuminate\Support\Facades\Route;


Route::post('/{tenant:uuid}/shop', FetchShops::class)->name('aurora.shops');
Route::post('/{tenant:uuid}/customer', FetchCustomers::class)->name('aurora.customers');
Route::post('/{tenant:uuid}/order', FetchOrders::class)->name('aurora.orders');

Route::post('/{tenant:uuid}/warehouse', FetchWarehouses::class)->name('aurora.warehouses');
Route::post('/{tenant:uuid}/warehouse_area', FetchWarehouseAreas::class)->name('aurora.warehouse_areas');
Route::post('/{tenant:uuid}/location', FetchLocations::class)->name('aurora.locations');
Route::post('/{tenant:uuid}/stock', FetchStocks::class)->name('aurora.stocks');

Route::post('/{tenant:uuid}/employee', FetchEmployees::class)->name('aurora.employee');
