<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 19:13:51 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Manufacturing\Artifact\UI\IndexArtifacts;
use App\Actions\Manufacturing\JobOrder\UI\ShowJobOrder;
use App\Actions\Manufacturing\Production\UI\CreateProduction;
use App\Actions\Manufacturing\Production\UI\EditProduction;
use App\Actions\Manufacturing\Production\UI\IndexProductions;
use App\Actions\Manufacturing\Production\UI\ShowProduction;
use App\Actions\Manufacturing\Production\UI\ShowProductionCrefts;
use App\Actions\Manufacturing\Production\UI\ShowProductionOperations;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexProductions::class)->name('index');
Route::get('create', CreateProduction::class)->name('create');

Route::prefix('{production}')
    ->group(function () {
        Route::get('', ShowProduction::class)->name('show');
        Route::get('edit', EditProduction::class)->name('edit');

        Route::name('show')
            ->group(function () {
                Route::name('.operations.')->prefix('operations')
                    ->group(function () {
                        Route::get('', ShowProductionOperations::class)->name('dashboard');
                        Route::get('artisans', ShowJobOrder::class)->name('artisans.index');

                        Route::get('job-orders', ShowJobOrder::class)->name('job-orders.index');
                        Route::get('job-orders/{jobOrder}', ShowJobOrder::class)->name('job-orders.show');
                    });

                Route::name('.crafts.')->prefix('crafts')
                    ->group(function () {
                        Route::get('', ShowProductionCrefts::class)->name('dashboard');

                        Route::get('raw-materials', ShowJobOrder::class)->name('raw_materials.index');
                        Route::get('artifacts', IndexArtifacts::class)->name('artifacts.index');
                    });
            });
    });
