<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Aug 2024 16:53:37 Central Indonesia Time, Bali Office, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */



use App\Actions\Inventory\OrgStock\UI\IndexOrgStocks;
use App\Actions\Inventory\OrgStock\UI\ShowOrgStock;
use Illuminate\Support\Facades\Route;

Route::prefix('stocks')->as('org_stocks.')->group(function () {
    Route::prefix('all')->as('all_org_stocks.')->group(function () {
        Route::get('/', [IndexOrgStocks::class, 'maya'])->name('index');

        Route::prefix('{orgStock}')->group(function () {
            Route::get('', [ShowOrgStock::class,'maya'])->name('show');
        });
    });
});
