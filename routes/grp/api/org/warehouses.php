<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Jan 2024 15:20:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\Pallet\ReturnPalletToCustomer;
use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Fulfilment\Pallet\UI\ShowPallet;
use App\Actions\Fulfilment\Pallet\UpdatePalletLocation;
use App\Actions\Fulfilment\UniversalScan\ShowUniversalScan;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\Location\UI\ShowLocation;
use App\Actions\Inventory\Warehouse\UI\IndexWarehouses;
use App\Actions\Inventory\WarehouseArea\UI\IndexWarehouseAreas;

Route::get('/', [IndexWarehouses::class, 'inOrganisation'])->name('index');
Route::get('areas', [IndexWarehouseAreas::class, 'inOrganisation'])->name('areas.index');

Route::get('/{warehouse:id}/locations', [IndexLocations::class, 'inWarehouse'])->name('locations.index');
Route::get('areas/{warehouseArea:id}/locations', [IndexLocations::class, 'inWarehouseArea'])->name('areas.locations.index');
Route::get('locations/{location}', ShowLocation::class)->name('locations.show');
Route::get('locations/{location}/pallets', [IndexPallets::class, 'inLocation'])->name('locations.pallets.index');
Route::patch('locations/{location}/pallets/{pallet}', UpdatePalletLocation::class)->name('pallets.location.update');

Route::get('scanners/{ulid}', ShowUniversalScan::class)->name('universal.scan.show');

Route::prefix("fulfilments/{fulfilment:id}")->name("fulfilments.")
    ->group(function () {
        Route::get('pallets', IndexPallets::class)->name('pallets.index');
        Route::get('pallets/{pallet}', ShowPallet::class)->name('pallets.show');
        Route::patch('pallets/{pallet}/return', ReturnPalletToCustomer::class)->name('pallets.return');
    });
