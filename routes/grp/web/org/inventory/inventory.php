<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 14:22:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Goods\Stock\UI\CreateStock;
use App\Actions\Goods\Stock\UI\EditStock;
use App\Actions\Goods\Stock\UI\ShowStock;
use App\Actions\Goods\StockFamily\ExportStockFamilies;
use App\Actions\Goods\StockFamily\UI\CreateStockFamily;
use App\Actions\Goods\StockFamily\UI\EditStockFamily;
use App\Actions\Goods\StockFamily\UI\RemoveStockFamily;
use App\Actions\Inventory\OrgStock\ExportOrgStocks;
use App\Actions\Inventory\OrgStock\UI\EditOrgStock;
use App\Actions\Inventory\OrgStock\UI\IndexOrgStocks;
use App\Actions\Inventory\OrgStockFamily\UI\IndexOrgStockFamilies;
use App\Actions\Inventory\OrgStockFamily\UI\ShowOrgStockFamily;
use App\Actions\Inventory\UI\ShowInventoryDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowInventoryDashboard::class)->name('dashboard');


Route::prefix('stocks')->as('org_stocks.')->group(function () {
    Route::get('/', IndexOrgStocks::class)->name('index');
    Route::get('/export', ExportOrgStocks::class)->name('export');

    Route::prefix('{orgStock}')->group(function () {
        Route::get('', ShowStock::class)->name('show');
        Route::get('edit', EditOrgStock::class)->name('edit');
    });
});


Route::prefix('families')->as('org_stock_families.')->group(function () {
    Route::get('', IndexOrgStockFamilies::class)->name('index');
    Route::get('/export', ExportStockFamilies::class)->name('export');
    Route::get('/create', CreateStockFamily::class)->name('create');

    Route::prefix('{orgStockFamily}')->group(function () {
        Route::get('', ShowOrgStockFamily::class)->name('show');
        Route::get('/edit', EditStockFamily::class)->name('edit');
        Route::get('/delete', RemoveStockFamily::class)->name('remove');

        Route::name('show.')->group(function () {
            Route::prefix('stocks')->as('org_stocks.')->group(function () {
                Route::get('/', [IndexOrgStocks::class, 'inStockFamily'])->name('index');
                Route::get('/export', [ExportOrgStocks::class, 'inStockFamily'])->name('export');
                Route::get('/create', [CreateStock::class, 'inStockFamily'])->name('create');

                Route::prefix('{orgStock}')->group(function () {
                    Route::get('', [ShowStock::class, 'inStockFamily'])->name('show');
                    Route::get('edit', [EditStock::class, 'inStockFamily'])->name('edit');
                });
            });
        });
    });
});
