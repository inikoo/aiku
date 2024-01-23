<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Jan 2024 15:20:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\Location\UI\ShowLocation;
use App\Actions\Inventory\Warehouse\UI\IndexWarehouses;
use App\Actions\Inventory\WarehouseArea\UI\IndexWarehouseAreas;

Route::get('/', [IndexWarehouses::class, 'inOrganisation'])->name('index');
Route::get('areas', [IndexWarehouseAreas::class, 'inOrganisation'])->name('areas.index');
Route::get('/{warehouse:id}/locations', [IndexLocations::class, 'inWarehouse'])->name('locations.index');

Route::get('areas/{warehouseArea:id}/locations', [IndexLocations::class, 'inWarehouseArea'])->name('areas.locations.index');
Route::get('locations/{location}', ShowLocation::class)->name('locations.show');
