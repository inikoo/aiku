<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 14:22:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Inventory\OrgStock\ExportStocks;
use App\Actions\Inventory\OrgStock\UI\CreateStock;
use App\Actions\Inventory\OrgStock\UI\EditStock;
use App\Actions\Inventory\OrgStock\UI\IndexOrgStocks;
use App\Actions\Inventory\OrgStock\UI\ShowStock;
use App\Actions\SupplyChain\StockFamily\ExportStockFamilies;
use App\Actions\SupplyChain\StockFamily\UI\CreateStockFamily;
use App\Actions\SupplyChain\StockFamily\UI\EditStockFamily;
use App\Actions\SupplyChain\StockFamily\UI\IndexStockFamilies;
use App\Actions\SupplyChain\StockFamily\UI\RemoveStockFamily;
use App\Actions\SupplyChain\StockFamily\UI\ShowStockFamily;
use App\Actions\UI\Inventory\ShowInventoryDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowInventoryDashboard::class)->name('dashboard');


Route::prefix('stocks')->as('org-stocks.')->group(function () {
    Route::get('/', IndexOrgStocks::class)->name('index');
    Route::get('/export', ExportStocks::class)->name('export');
    Route::get('/create', CreateStock::class)->name('create');

    Route::prefix('{orgStock}')->group(function () {
        Route::get('', ShowStock::class)->name('show');
        Route::get('edit', EditStock::class)->name('edit');
    });
});


Route::prefix('families')->as('org-stock-families.')->group(function () {
    Route::get('', IndexStockFamilies::class)->name('index');
    Route::get('/export', ExportStockFamilies::class)->name('export');
    Route::get('/create', CreateStockFamily::class)->name('create');

    Route::prefix('{orgStockFamily}')->group(function () {
        Route::get('', ShowStockFamily::class)->name('show');
        Route::get('/edit', EditStockFamily::class)->name('edit');
        Route::get('/delete', RemoveStockFamily::class)->name('remove');


        Route::prefix('stocks')->as('org-stocks.')->group(function () {
            Route::get('/', [IndexOrgStocks::class, 'inStockFamily'])->name('index');
            Route::get('/export', [ExportStocks::class, 'inStockFamily'])->name('export');
            Route::get('/create', [CreateStock::class, 'inStockFamily'])->name('create');

            Route::prefix('{orgStock}')->group(function () {
                Route::get('', [ShowStock::class, 'inStockFamily'])->name('show');
                Route::get('edit', [EditStock::class, 'inStockFamily'])->name('edit');
            });
        });
    });
});
