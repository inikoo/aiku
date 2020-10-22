<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 22 Oct 2020 02:39:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Legacy\StockController;


;

Route::post(
    '/{legacy_location_id}/{legacy_stock_id}', [
                      StockController::class,
                      'update_location_stock'
                  ]
)->name('update_location_stock');
