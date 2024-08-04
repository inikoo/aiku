<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 04:20:03 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Goods\Stock\ExportStocks;
use App\Actions\Goods\Stock\UI\CreateStock;
use App\Actions\Goods\Stock\UI\EditStock;
use App\Actions\Goods\Stock\UI\IndexStocks;
use App\Actions\Goods\Stock\UI\ShowStock;
use App\Actions\Goods\StockFamily\ExportStockFamilies;
use App\Actions\Goods\StockFamily\UI\CreateStockFamily;
use App\Actions\Goods\StockFamily\UI\EditStockFamily;
use App\Actions\Goods\StockFamily\UI\IndexStockFamilies;
use App\Actions\Goods\StockFamily\UI\RemoveStockFamily;
use App\Actions\Goods\StockFamily\UI\ShowStockFamily;
use App\Actions\UI\Goods\ShowGoodsDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowGoodsDashboard::class)->name('dashboard');


Route::prefix('stocks')->as('stocks.')->group(function () {
    Route::get('/', IndexStocks::class)->name('index');
    Route::get('/export', ExportStocks::class)->name('export');
    Route::get('/create', CreateStock::class)->name('create');


    Route::prefix('active')->as('active_stocks.')->group(function () {
        Route::get('/', [IndexStocks::class, 'active'])->name('index');
        Route::prefix('{stock}')->group(function () {
            Route::get('', [ShowStock::class, 'active'])->name('show');
            Route::get('edit', [EditStock::class, 'active'])->name('edit');
        });
    });


    Route::prefix('in-process-stocks')->as('in_process_stocks.')->group(function () {
        Route::get('/', [IndexStocks::class, 'inProcess'])->name('index');
        Route::prefix('{stock}')->group(function () {
            Route::get('', [ShowStock::class, 'inProcess'])->name('show');
            Route::get('edit', [EditStock::class, 'inProcess'])->name('edit');
        });
    });

    Route::prefix('discontinuing-stocks')->as('discontinuing_stocks.')->group(function () {
        Route::get('/', [IndexStocks::class, 'discontinuing'])->name('index');
        Route::prefix('{stock}')->group(function () {
            Route::get('', [ShowStock::class, 'discontinuing'])->name('show');
            Route::get('edit', [EditStock::class, 'discontinuing'])->name('edit');
        });
    });

    Route::prefix('discontinued-stocks')->as('discontinued_stocks.')->group(function () {
        Route::get('/', [IndexStocks::class, 'discontinued'])->name('index');
        Route::prefix('{stock}')->group(function () {
            Route::get('', [ShowStock::class, 'discontinued'])->name('show');
            Route::get('edit', [EditStock::class, 'discontinued'])->name('edit');
        });
    });


    Route::prefix('{stock}')->group(function () {
        Route::get('', ShowStock::class)->name('show');
        Route::get('edit', EditStock::class)->name('edit');
    });
});







Route::prefix('families')->as('stock-families.')->group(function () {
    Route::get('', IndexStockFamilies::class)->name('index');
    Route::get('/export', ExportStockFamilies::class)->name('export');
    Route::get('/create', CreateStockFamily::class)->name('create');

    Route::prefix('{stockFamily}')->group(function () {
        Route::get('', ShowStockFamily::class)->name('show');
        Route::get('/edit', EditStockFamily::class)->name('edit');
        Route::get('/delete', RemoveStockFamily::class)->name('remove');


        Route::prefix('stocks')->as('show.stocks.')->group(function () {
            Route::get('/', [IndexStocks::class, 'inStockFamily'])->name('index');
            Route::get('/export', [ExportStocks::class, 'inStockFamily'])->name('export');
            Route::get('/create', [CreateStock::class, 'inStockFamily'])->name('create');

            Route::prefix('{stock}')->group(function () {
                Route::get('', [ShowStock::class, 'inStockFamily'])->name('show');
                Route::get('edit', [EditStock::class, 'inStockFamily'])->name('edit');
            });
        });
    });
});
