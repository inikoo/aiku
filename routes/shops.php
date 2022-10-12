<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 16:27:58 Central European Summer Time, Kuala Lumpur, Malaysia
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
Route::get('/{shop}', ShowShop::class)->name('show');
