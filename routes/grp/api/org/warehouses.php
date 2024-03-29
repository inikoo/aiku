<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Jan 2024 15:20:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\Pallet\ReturnPalletToCustomer;
use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Fulfilment\Pallet\UI\ShowPallet;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\Fulfilment\Pallet\UpdatePalletLocation;
use App\Actions\Fulfilment\PalletDelivery\UI\IndexPalletDeliveries;
use App\Actions\Fulfilment\PalletDelivery\UI\ShowPalletDelivery;
use App\Actions\Fulfilment\PalletReturn\UI\IndexPalletReturns;
use App\Actions\Fulfilment\PalletReturn\UI\ShowPalletReturn;
use App\Actions\Fulfilment\UniversalScan\ShowUniversalScan;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\Location\UI\ShowLocation;
use App\Actions\Inventory\Warehouse\UI\IndexWarehouses;
use App\Actions\Inventory\WarehouseArea\UI\IndexWarehouseAreas;

Route::get('/', [IndexWarehouses::class, 'inOrganisation'])->name('index');
Route::get('areas', [IndexWarehouseAreas::class, 'inOrganisation'])->name('areas.index');

Route::prefix("{warehouse:id}")->name("warehouses.")
    ->group(function () {
        Route::get('scanners/{ulid}', ShowUniversalScan::class)->name('universal.scan.show');

        Route::get('locations', [IndexLocations::class, 'inWarehouse'])->name('locations.index');
        Route::get('areas/{warehouseArea:id}/locations', [IndexLocations::class, 'inWarehouseArea'])->name('areas.locations.index');
        Route::get('locations/{location}', [ShowLocation::class, 'inWarehouse'])->name('locations.show');
        Route::patch('locations/{location}/pallets/{pallet:id}', UpdatePalletLocation::class)->name('pallets.location.update')->withoutScopedBindings();

        Route::prefix("fulfilments/{fulfilment}")->name("fulfilments.")->group(function () {
            Route::get('locations/{location}/pallets', [IndexPallets::class, 'inLocation'])->name('locations.pallets.index');
            Route::get('pallets', IndexPallets::class)->name('pallets.index');
            Route::get('pallets/{pallet:id}', ShowPallet::class)->name('pallets.show')->withoutScopedBindings();
            Route::patch('pallets/{pallet:id}/return', ReturnPalletToCustomer::class)->name('pallets.return')->withoutScopedBindings();
            Route::patch('pallets/{pallet:id}', [UpdatePallet::class, 'fromApi'])->name('pallets.update')->withoutScopedBindings();
        });

        Route::prefix('pallet-deliveries')->name('pallet-delivery.')->group(function () {
            Route::get('/', [IndexPalletDeliveries::class, 'inWarehouse'])->name('index');
            Route::get('{palletDelivery}', [ShowPalletDelivery::class, 'inWarehouse'])->name('show');
        });

        Route::prefix('pallet-returns')->name('pallet-return.')->group(function () {
            Route::get('/', [IndexPalletReturns::class, 'inWarehouse'])->name('index');
            Route::get('{palletReturn}', [ShowPalletReturn::class, 'inWarehouse'])->name('show');
        });
    });
