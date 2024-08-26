<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jan 2024 14:40:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Fulfilment\Pallet\UI\ShowPallet;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\Location\UI\ShowLocation;
use App\Actions\Inventory\WarehouseArea\UI\IndexWarehouseAreas;
use App\Actions\Inventory\WarehouseArea\UI\ShowWarehouseArea;

Route::scopeBindings()->prefix('areas')->name('warehouse-areas.')->group(function () {
    Route::get('', [IndexWarehouseAreas::class, 'inOrganisation'])->name('index');
    Route::scopeBindings()->prefix('{warehouseArea}')->group(function () {
        Route::get('', ShowWarehouseArea::class)->name('show');
        Route::scopeBindings()->prefix('locations')->name('show.locations.')->group(function () {
            Route::get('', IndexLocations::class)->name('index');
            Route::scopeBindings()->prefix('{location}')->group(function () {
                Route::get('', ShowLocation::class)->name('show');
            });
        });
    });
});


Route::scopeBindings()->prefix('locations')->name('locations.')->group(function () {
    Route::get('', [IndexLocations::class, 'inWarehouse'])->name('index');
    Route::get('/code/{location:code}', [ShowLocation::class, 'inWarehouse'])->name('code.show')->withoutScopedBindings();
    Route::scopeBindings()->prefix('{location}')->group(function () {
        Route::get('', [ShowLocation::class, 'inWarehouse'])->name('show');
        Route::get('pallets/{pallet}', [ShowPallet::class, 'inLocation'])->name('show.pallets.show');
    });
});
