<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 19:13:51 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Manufacturing\Production\UI\CreateProduction;
use App\Actions\Manufacturing\Production\UI\EditProduction;
use App\Actions\Manufacturing\Production\UI\IndexProductions;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexProductions::class)->name('index');
Route::get('create', CreateProduction::class)->name('create');

Route::prefix('{production}')
    ->group(function () {
        Route::get('edit', EditProduction::class)->name('edit');
        Route::name('show')
            ->group(function () {




            });
    });
