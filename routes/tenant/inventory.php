<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 16:34:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Inventory\Location\UI\CreateLocation;
use App\Actions\Inventory\Location\UI\EditLocation;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\Location\UI\ShowLocation;
use App\Actions\Inventory\Stock\UI\CreateStock;
use App\Actions\Inventory\Stock\UI\EditStock;
use App\Actions\Inventory\Stock\UI\IndexStocks;
use App\Actions\Inventory\Stock\UI\ShowStock;
use App\Actions\Inventory\StockFamily\UI\CreateStockFamily;
use App\Actions\Inventory\StockFamily\UI\EditStockFamily;
use App\Actions\Inventory\StockFamily\UI\IndexStockFamilies;
use App\Actions\Inventory\StockFamily\UI\ShowStockFamily;
use App\Actions\Inventory\Warehouse\UI\CreateWarehouse;
use App\Actions\Inventory\Warehouse\UI\EditWarehouse;
use App\Actions\Inventory\Warehouse\UI\IndexWarehouses;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\Inventory\WarehouseArea\UI\CreateWarehouseArea;
use App\Actions\Inventory\WarehouseArea\UI\EditWarehouseArea;
use App\Actions\Inventory\WarehouseArea\UI\IndexWarehouseAreas;
use App\Actions\Inventory\WarehouseArea\UI\ShowWarehouseArea;
use App\Actions\UI\Inventory\InventoryDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', InventoryDashboard::class)->name('dashboard');

Route::get('/warehouses', IndexWarehouses::class)->name('warehouses.index');
Route::get('/warehouses/create', CreateWarehouse::class)->name('warehouses.create');
Route::get('/warehouses/{warehouse}', ShowWarehouse::class)->name('warehouses.show');
Route::get('/warehouses/{warehouse}/edit', EditWarehouse::class)->name('warehouses.edit');


Route::get('/areas', [IndexWarehouseAreas::class, 'inOrganisation'])->name('warehouse_areas.index');
Route::get('/areas/create', CreateWarehouseArea::class)->name('warehouse_areas.create');
Route::get('/areas/{warehouseArea}', [ShowWarehouseArea::class, 'inOrganisation'])->name('warehouse_areas.show');
Route::get('/areas/{warehouseArea}/edit', [EditWarehouseArea::class, 'inOrganisation'])->name('warehouse_areas.edit');


Route::get('/locations', [IndexLocations::class, 'inOrganisation'])->name('locations.index');
Route::get('/locations/create', CreateLocation::class)->name('locations.create');
Route::get('/locations/{location}', [ShowLocation::class, 'inOrganisation'])->name('locations.show');
Route::get('/locations/{location}/edit', [EditLocation::class, 'inOrganisation'])->name('locations.edit');


Route::scopeBindings()->group(function () {
    Route::get('/areas/{warehouseArea}/locations', [IndexLocations::class, 'inWarehouseArea'])->name('warehouse_areas.show.locations.index');
    Route::get('/areas/{warehouseArea}/locations/{location}', [ShowLocation::class, 'inWarehouseArea'])->name('warehouse_areas.show.locations.show');


    Route::get('/warehouses/{warehouse}/areas', [IndexWarehouseAreas::class, 'inWarehouse'])->name('warehouses.show.warehouse_areas.index');
    Route::get('/warehouses/{warehouse}/areas/{warehouseArea}', [ShowWarehouseArea::class, 'inWarehouse'])->name('warehouses.show.warehouse_areas.show');

    Route::get('/warehouses/{warehouse}/areas/{warehouseArea}/locations', [IndexLocations::class, 'InWarehouseInWarehouseArea'])->name('warehouses.show.warehouse_areas.show.locations.index');
    Route::get('/warehouses/{warehouse}/areas/{warehouseArea}/locations/{location}', [ShowLocation::class, 'InWarehouseInWarehouseArea'])->name('warehouses.show.warehouse_areas.show.locations.show');


    Route::get('/warehouses/{warehouse}/locations', [IndexLocations::class, 'inWarehouse'])->name('warehouses.show.locations.index');
    Route::get('/warehouses/{warehouse}/locations/{location}', [ShowLocation::class, 'inWarehouse'])->name('warehouses.show.locations.show');
});

Route::get('/families', IndexStockFamilies::class)->name('stock-families.index');
Route::get('/families/create', CreateStockFamily::class)->name('stock-families.create');
Route::get('/families/{stockFamily:slug}', ShowStockFamily::class)->name('stock-families.show');
Route::get('/families/{stockFamily:slug}/edit', EditStockFamily::class)->name('stock-families.edit');
Route::get('/families/{stockFamily:slug}/stocks', [IndexStocks::class, 'inStockFamily'])->name('stock-families.show.stocks.index');


Route::get('/stocks', [IndexStocks::class, 'inStockFamily'])->name('stocks.index');
Route::get('/stocks/create', CreateStock::class)->name('stocks.create');
Route::get('/stocks/{stock}', ShowStock::class)->name('stocks.show');
Route::get('/stocks/{stock}/edit', EditStock::class)->name('stocks.edit');
