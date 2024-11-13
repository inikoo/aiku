<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 13-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

use App\Actions\Inventory\WarehouseArea\StoreWarehouseArea;
use App\Actions\Inventory\WarehouseArea\UpdateWarehouseArea;
use Illuminate\Support\Facades\Route;

Route::patch('/warehouse/{warehouse:id}/areas/{warehouseArea:id}/update', UpdateWarehouseArea::class)->name('warehouse.warehouse-area.update');
Route::post('/warehouse/{warehouse:id}/areas', StoreWarehouseArea::class)->name('warehouse.warehouse-area.store');
