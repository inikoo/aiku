<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jan 2024 14:40:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Inventory\Location\ExportLocations;
use App\Actions\Inventory\Location\UI\EditLocation;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\Location\UI\ShowLocation;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\Inventory\WarehouseArea\UI\CreateWarehouseArea;
use App\Actions\Inventory\WarehouseArea\UI\EditWarehouseArea;
use App\Actions\Inventory\WarehouseArea\UI\IndexWarehouseAreas;
use App\Actions\Inventory\WarehouseArea\UI\ShowWarehouseArea;

Route::get('/', ShowWarehouse::class)->name('dashboard');
Route::get('/areas', [IndexWarehouseAreas::class, 'inOrganisation'])->name('warehouse-areas.index');
Route::get('/areas/create', CreateWarehouseArea::class)->name('warehouse-areas.create');
Route::get('/areas/{warehouseArea}', [ShowWarehouseArea::class, 'inOrganisation'])->name('warehouse-areas.show');
Route::get('/areas/{warehouseArea}/edit', [EditWarehouseArea::class, 'inOrganisation'])->name('warehouse-areas.edit');


Route::get('/locations/export', ExportLocations::class)->name('locations.export');

Route::get('/locations', [IndexLocations::class, 'inOrganisation'])->name('locations.index');
Route::get('/locations/{location}', [ShowLocation::class, 'inOrganisation'])->name('locations.show');
Route::get('/locations/{location}/edit', [EditLocation::class, 'inOrganisation'])->name('locations.edit');
