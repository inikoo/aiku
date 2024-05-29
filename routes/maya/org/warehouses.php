<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Jan 2024 15:20:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Fulfilment\Pallet\UI\IndexPalletsInDelivery;
use App\Actions\Fulfilment\Pallet\UI\IndexPalletsInReturn;
use App\Actions\Fulfilment\Pallet\UI\ShowPallet;
use App\Actions\Fulfilment\PalletDelivery\UI\IndexPalletDeliveries;
use App\Actions\Fulfilment\PalletDelivery\UI\ShowPalletDelivery;
use App\Actions\Fulfilment\PalletReturn\UI\IndexPalletReturns;
use App\Actions\Fulfilment\PalletReturn\UI\ShowPalletReturn;
use App\Actions\Fulfilment\UniversalScan\ShowUniversalScan;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\Location\UI\ShowLocation;
use App\Actions\Inventory\Warehouse\UI\IndexWarehouses;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\Inventory\WarehouseArea\UI\IndexWarehouseAreas;

Route::get('/', IndexWarehouses::class)->name('index');
Route::get('areas', [IndexWarehouseAreas::class, 'inOrganisation'])->name('areas.index');

Route::prefix("{warehouse:id}")->name("warehouses.")
    ->group(function () {
        Route::get('/', ShowWarehouse::class)->name('show')->withoutScopedBindings();
        Route::get('scanners/{ulid}', ShowUniversalScan::class)->name('universal.scan.show');

        Route::get('locations', [IndexLocations::class, 'inWarehouse'])->name('locations.index');
        Route::get('areas/{warehouseArea:id}/locations', [IndexLocations::class, 'inWarehouseArea'])->name('areas.locations.index')->withoutScopedBindings();
        Route::get('locations/{location:id}', [ShowLocation::class, 'inWarehouse'])->name('locations.show')->withoutScopedBindings();

        Route::prefix("fulfilments/{fulfilment:id}")->name("fulfilments.")->group(function () {
            Route::get('locations/{location:id}/pallets', [IndexPallets::class, 'inLocation'])->name('locations.pallets.index')->withoutScopedBindings();
            Route::get('pallets', IndexPallets::class)->name('pallets.index');
            Route::get('pallets/{pallet:id}', ShowPallet::class)->name('pallets.show')->withoutScopedBindings();
        });

        Route::prefix('pallet-deliveries')->name('pallet-delivery.')->group(function () {
            Route::get('/', [IndexPalletDeliveries::class, 'inWarehouse'])->name('index');
            Route::get('{palletDelivery:id}', [ShowPalletDelivery::class, 'inWarehouse'])->name('show')->withoutScopedBindings();
            Route::get('{palletDelivery:id}/pallets', IndexPalletsInDelivery::class)->name('pallets.index')->withoutScopedBindings();
        });

        Route::prefix('pallet-returns')->name('pallet-return.')->group(function () {
            Route::get('/', [IndexPalletReturns::class, 'inWarehouse'])->name('index');
            Route::get('{palletReturn:id}', [ShowPalletReturn::class, 'inWarehouse'])->name('show')->withoutScopedBindings();
            Route::get('{palletReturn:id}/pallets', IndexPalletsInReturn::class)->name('pallets.index')->withoutScopedBindings();
        });
    });
