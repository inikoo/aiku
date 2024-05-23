<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jan 2024 11:31:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Product\UI\CreateProduct;
use App\Actions\Catalogue\Product\UI\IndexProducts;
use App\Actions\Devel\UI\EditDummy;
use App\Actions\Devel\UI\IndexDummies;
use App\Actions\Devel\UI\ShowDummy;
use App\Actions\Devel\UI\ShowDummyDashboard;
use Illuminate\Support\Facades\Route;

Route::get('', ShowDummyDashboard::class)->name('dashboard');


Route::name("shipping.")->prefix('shipping')
    ->group(function () {
        Route::get('', IndexProducts::class)->name('index');
        Route::get('create', CreateProduct::class)->name('create');


    });

Route::name("charges.")->prefix('charges')
    ->group(function () {
        Route::get('', IndexDummies::class)->name('index');
        Route::get('create', EditDummy::class)->name('create');

        Route::prefix('{charge}')->group(function () {
            Route::get('', ShowDummy::class)->name('show');
            Route::get('edit', EditDummy::class)->name('edit');
        });
    });


Route::name("services.")->prefix('services')
    ->group(function () {
        Route::get('', IndexDummies::class)->name('index');
        Route::get('create', EditDummy::class)->name('create');

        Route::prefix('{service}')->group(function () {
            Route::get('', ShowDummy::class)->name('show');
            Route::get('edit', EditDummy::class)->name('edit');
        });
    });
