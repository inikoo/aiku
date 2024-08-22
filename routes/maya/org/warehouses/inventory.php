<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Aug 2024 15:40:33 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Fulfilment\Pallet\UI\ShowPallet;
use App\Actions\Inventory\OrgStock\UI\IndexOrgStocks;
use App\Actions\Inventory\OrgStock\UI\ShowOrgStock;
use Illuminate\Support\Facades\Route;

Route::prefix('stocks')->as('org_stocks.')->group(function () {
    Route::get('/', IndexOrgStocks::class)->name('index');
    Route::get('{orgStock:id}', ShowOrgStock::class)->name('show')->withoutScopedBindings();
});

Route::prefix('pallets')->as('org_stocks.')->group(function () {
    Route::get('/', IndexPallets::class)->name('index');
    Route::get('{pallet:id}', ShowPallet::class)->name('show');
});
