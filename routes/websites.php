<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 13 Oct 2022 15:46:44 Central European Summer Time, Plane Malaga - East Midlands UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Actions\Inventory\Location\IndexLocations;
use App\Actions\Inventory\Location\ShowLocation;
use App\Actions\Inventory\ShowInventoryDashboard;
use App\Actions\Inventory\Warehouse\IndexWarehouses;
use App\Actions\Inventory\Warehouse\ShowWarehouse;
use App\Actions\Inventory\WarehouseArea\IndexWarehouseAreas;
use App\Actions\Inventory\WarehouseArea\ShowWarehouseArea;
use App\Actions\Marketing\SHop\IndexShops;
use App\Actions\Marketing\Shop\ShowShop;


Route::get('/', IndexShops::class)->name('index');
Route::get('/{website}', ShowShop::class)->name('show');
