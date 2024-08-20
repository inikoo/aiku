<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Aug 2024 16:53:37 Central Indonesia Time, Bali Office, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */



use App\Actions\Inventory\OrgStock\UI\IndexOrgStocks;
use App\Actions\Inventory\OrgStock\UI\ShowOrgStock;
use App\Actions\Inventory\OrgStockFamily\UI\IndexOrgStockFamilies;
use App\Actions\Inventory\OrgStockFamily\UI\ShowOrgStockFamily;
use Illuminate\Support\Facades\Route;

Route::prefix('stocks')->as('org_stocks.')->group(function () {
    Route::prefix('all')->as('all_org_stocks.')->group(function () {
        Route::get('/', [IndexOrgStocks::class, 'maya'])->name('index');

        Route::prefix('{orgStock:id}')->group(function () {
            Route::get('', [ShowOrgStock::class,'maya'])->name('show')->withoutScopedBindings();
        });
    });
    Route::prefix('families')->as('org_stock_families.')->group(function () {
        Route::get('/', [IndexOrgStockFamilies::class, 'maya'])->name('index');

        Route::prefix('{orgStockFamily:id}')->group(function () {
            Route::get('', [ShowOrgStockFamily::class,'maya'])->name('show')->withoutScopedBindings();
        });
    });
});
