<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 16:34:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Inventory\Location\ExportLocations;
use App\Actions\Inventory\Location\UI\CreateLocation;
use App\Actions\Inventory\Location\UI\EditLocation;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\Location\UI\RemoveLocation;
use App\Actions\Inventory\Location\UI\ShowLocation;
use App\Actions\Inventory\Stock\ExportStocks;
use App\Actions\Inventory\Stock\UI\CreateStock;
use App\Actions\Inventory\Stock\UI\EditStock;
use App\Actions\Inventory\Stock\UI\IndexStocks;
use App\Actions\Inventory\Stock\UI\ShowStock;
use App\Actions\Inventory\StockFamily\ExportStockFamilies;
use App\Actions\Inventory\StockFamily\UI\CreateStockFamily;
use App\Actions\Inventory\StockFamily\UI\EditStockFamily;
use App\Actions\Inventory\StockFamily\UI\IndexStockFamilies;
use App\Actions\Inventory\StockFamily\UI\ShowStockFamily;
use App\Actions\Inventory\Warehouse\ExportWarehouses;
use App\Actions\Inventory\Warehouse\UI\CreateWarehouse;
use App\Actions\Inventory\Warehouse\UI\EditWarehouse;
use App\Actions\Inventory\Warehouse\UI\IndexWarehouses;
use App\Actions\Inventory\Warehouse\UI\RemoveWarehouse;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\Inventory\WarehouseArea\ExportWarehouseAreas;
use App\Actions\Inventory\WarehouseArea\UI\CreateWarehouseArea;
use App\Actions\Inventory\WarehouseArea\UI\CreateWarehouseAreas;
use App\Actions\Inventory\WarehouseArea\UI\EditWarehouseArea;
use App\Actions\Inventory\WarehouseArea\UI\IndexWarehouseAreas;
use App\Actions\Inventory\WarehouseArea\UI\RemoveWarehouseArea;
use App\Actions\Inventory\WarehouseArea\UI\ShowWarehouseArea;
use App\Actions\UI\Inventory\InventoryDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', InventoryDashboard::class)->name('dashboard');

Route::get('/warehouses/export', ExportWarehouses::class)->name('warehouses.export');

Route::get('/warehouses', IndexWarehouses::class)->name('warehouses.index');
Route::get('/warehouses/create', CreateWarehouse::class)->name('warehouses.create');
Route::get('/warehouses/{warehouse}', ShowWarehouse::class)->name('warehouses.show');

Route::get('/warehouses/{warehouse}/edit', EditWarehouse::class)->name('warehouses.edit');
Route::get('/warehouses/{warehouse}/delete', RemoveWarehouse::class)->name('warehouses.remove');

Route::get('/areas/export', ExportWarehouseAreas::class)->name('warehouse-areas.export');

Route::get('/areas', [IndexWarehouseAreas::class, 'inTenant'])->name('warehouse-areas.index');
Route::get('/areas/create', CreateWarehouseArea::class)->name('warehouse-areas.create');
Route::get('/areas/{warehouseArea}', [ShowWarehouseArea::class, 'inTenant'])->name('warehouse-areas.show');
Route::get('/areas/{warehouseArea}/edit', [EditWarehouseArea::class, 'inTenant'])->name('warehouse-areas.edit');
Route::get('/areas/{warehouseArea}/delete', [RemoveWarehouseArea::class, 'inTenant'])->name('warehouse-areas.remove');
Route::get('/warehouses/{warehouse}/areas/create-multi', CreateWarehouseAreas::class)->name('warehouses.show.warehouse-areas.create-multi');
Route::get('/warehouses/{warehouse}/areas/create-multi/clear', CreateWarehouseAreas::class)->name('warehouses.show.warehouse-areas.create-multi-clear');

Route::get('/locations/export', ExportLocations::class)->name('locations.export');

Route::get('/locations', [IndexLocations::class, 'inTenant'])->name('locations.index');
Route::get('/locations/{location}', [ShowLocation::class, 'inTenant'])->name('locations.show');
Route::get('/locations/{location}/edit', [EditLocation::class, 'inTenant'])->name('locations.edit');
Route::get('/locations/{location}/delete', RemoveLocation::class)->name('locations.remove');


Route::scopeBindings()->group(function () {
    Route::get('/areas/{warehouseArea}/locations', [IndexLocations::class, 'inWarehouseArea'])->name('warehouse-areas.show.locations.index');
    Route::get('/areas/{warehouseArea}/locations/{location}', [ShowLocation::class, 'inWarehouseArea'])->name('warehouse-areas.show.locations.show');
    Route::get('/areas/{warehouseArea}/locations/create', [CreateLocation::class, 'inWarehouseArea'])->name('warehouse-areas.show.locations.create');
    Route::get('/areas/{warehouseArea}/locations/{location}/edit', [EditLocation::class, 'inWarehouseArea'])->name('warehouse-areas.show.locations.edit');
    Route::get('/areas/{warehouseArea}/locations/{location}/delete', [RemoveLocation::class, 'inWarehouseArea'])->name('warehouse-areas.show.locations.remove');

    Route::get('/warehouses/{warehouse}/areas', [IndexWarehouseAreas::class, 'inWarehouse'])->name('warehouses.show.warehouse-areas.index');
    Route::get('/warehouses/{warehouse}/areas/create', CreateWarehouseArea::class)->name('warehouses.show.warehouse-areas.create');
    Route::get('/warehouses/{warehouse}/areas/{warehouseArea}', [ShowWarehouseArea::class, 'inWarehouse'])->name('warehouses.show.warehouse-areas.show');
    Route::get('/warehouses/{warehouse}/areas/{warehouseArea}/edit', [EditWarehouseArea::class, 'inWarehouse'])->name('warehouses.show.warehouse-areas.edit');
    Route::get('/warehouses/{warehouse}/areas/{warehouseArea}/delete', [RemoveWarehouseArea::class, 'inWarehouse'])->name('warehouses.show.warehouse-areas.remove');


    Route::get('/warehouses/{warehouse}/areas/{warehouseArea}/locations', [IndexLocations::class, 'inWarehouseInWarehouseArea'])->name('warehouses.show.warehouse-areas.show.locations.index');
    Route::get('/warehouses/{warehouse}/areas/{warehouseArea}/locations/create', [CreateLocation::class, 'inWarehouseInWarehouseArea'])->name('warehouses.show.warehouse-areas.show.locations.create');
    Route::get('/warehouses/{warehouse}/areas/{warehouseArea}/locations/{location}/edit', [EditLocation::class, 'inWarehouseInWarehouseArea'])->name('warehouses.show.warehouse-areas.show.locations.edit');
    Route::get('/warehouses/{warehouse}/areas/{warehouseArea}/locations/{location}', [ShowLocation::class, 'inWarehouseInWarehouseArea'])->name('warehouses.show.warehouse-areas.show.locations.show');
    Route::get('/warehouses/{warehouse}/areas/{warehouseArea}/locations/{location}/delete', [RemoveLocation::class, 'inWarehouseInWarehouseArea'])->name('warehouses.show.warehouse-areas.show.locations.remove');

    Route::get('/warehouses/{warehouse}/locations', [IndexLocations::class, 'inWarehouse'])->name('warehouses.show.locations.index');
    Route::get('/warehouses/{warehouse}/locations/create', [CreateLocation::class, 'inWarehouse'])->name('warehouses.show.locations.create');
    Route::get('/warehouses/{warehouse}/locations/{location}', [ShowLocation::class, 'inWarehouse'])->name('warehouses.show.locations.show');
    Route::get('/warehouses/{warehouse}/locations/{location}/edit', [EditLocation::class, 'inWarehouse'])->name('warehouses.show.locations.edit');
    Route::get('/warehouses/{warehouse}/locations/{location}/delete', [RemoveLocation::class,'inWarehouse'])->name('warehouses.show.locations.remove');
});

Route::get('/families/export', ExportStockFamilies::class)->name('stock-families.export');

Route::get('/families', IndexStockFamilies::class)->name('stock-families.index');
Route::get('/families/create', CreateStockFamily::class)->name('stock-families.create');
Route::get('/families/{stockFamily}', ShowStockFamily::class)->name('stock-families.show');
Route::get('/families/{stockFamily}/edit', EditStockFamily::class)->name('stock-families.edit');
Route::get('/families/{stockFamily}/delete', EditStockFamily::class)->name('stock-families.remove');
Route::get('/families/{stockFamily}/stocks', [IndexStocks::class, 'inStockFamily'])->name('stock-families.show.stocks.index');
Route::get('/families/{stockFamily}/stocks/{stock}', [ShowStock::class, 'inStockFamily'])->name('stock-families.show.stocks.show');


Route::get('/stocks/export', ExportStocks::class)->name('stocks.export');

Route::get('/stocks', IndexStocks::class)->name('stocks.index');
Route::get('/stocks/create', CreateStock::class)->name('stocks.create');
Route::get('/stocks/{stock}', ShowStock::class)->name('stocks.show');
Route::get('/stocks/{stock}/edit', EditStock::class)->name('stocks.edit');
