<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jan 2024 14:40:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Goods\Stock\UI\CreateStock;
use App\Actions\Goods\Stock\UI\EditStock;
use App\Actions\Goods\Stock\UI\ShowStock;
use App\Actions\Goods\StockFamily\ExportStockFamilies;
use App\Actions\Goods\StockFamily\UI\CreateStockFamily;
use App\Actions\Goods\StockFamily\UI\EditStockFamily;
use App\Actions\Goods\StockFamily\UI\IndexStockFamilies;
use App\Actions\Goods\StockFamily\UI\RemoveStockFamily;
use App\Actions\Goods\StockFamily\UI\ShowStockFamily;
use App\Actions\Inventory\OrgStock\ExportOrgStocks;
use App\Actions\Inventory\OrgStock\UI\IndexOrgStocks;
use App\Actions\Inventory\UI\ShowInventoryDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowInventoryDashboard::class)->name('dashboard');


Route::prefix('stocks')->as('org-stocks.')->group(function () {
    Route::get('/', IndexOrgStocks::class)->name('index');
    Route::get('/export', ExportOrgStocks::class)->name('export');
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
            Route::get('/export', [ExportOrgStocks::class, 'inStockFamily'])->name('export');
            Route::get('/create', [CreateStock::class, 'inStockFamily'])->name('create');

            Route::prefix('{orgStock}')->group(function () {
                Route::get('', [ShowStock::class, 'inStockFamily'])->name('show');
                Route::get('edit', [EditStock::class, 'inStockFamily'])->name('edit');
            });
        });
    });
});
