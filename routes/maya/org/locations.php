<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Aug 2024 16:53:37 Central Indonesia Time, Bali Office, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\Location\UI\ShowLocation;
use App\Actions\Inventory\OrgStock\UI\IndexOrgStocks;
use App\Actions\Inventory\OrgStock\UI\ShowOrgStock;
use App\Actions\Inventory\OrgStockFamily\UI\IndexOrgStockFamilies;
use App\Actions\Inventory\OrgStockFamily\UI\ShowOrgStockFamily;
use Illuminate\Support\Facades\Route;

Route::prefix('all')->as('all_locations.')->group(function () {
        Route::get('/', [IndexLocations::class, 'maya'])->name('index');
});

Route::prefix('show/{location:id}')->group(function () {
    Route::get('', [ShowLocation::class,'maya'])->name('showx')->withoutScopedBindings();
});
