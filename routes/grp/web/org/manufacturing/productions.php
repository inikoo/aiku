<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 19:13:51 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Devel\UI\CreateDummy;
use App\Actions\Manufacturing\Artefact\UI\IndexArtefacts;
use App\Actions\Manufacturing\JobOrder\UI\ShowJobOrder;
use App\Actions\Manufacturing\Production\UI\CreateProduction;
use App\Actions\Manufacturing\Production\UI\EditProduction;
use App\Actions\Manufacturing\Production\UI\IndexProductions;
use App\Actions\Manufacturing\Production\UI\ShowProduction;
use App\Actions\Manufacturing\Production\UI\ShowProductionCrafts;
use App\Actions\Manufacturing\Production\UI\ShowProductionOperations;
use App\Actions\Manufacturing\RawMaterial\UI\IndexRawMaterials;
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
                        Route::get('', ShowProductionCrafts::class)->name('dashboard');

                        Route::get('raw-materials', IndexRawMaterials::class)->name('raw_materials.index');



                        Route::get('artefacts', IndexArtefacts::class)->name('artefacts.index');
                        Route::get('artefacts/create', CreateDummy::class)->name('artefacts.create');

                    });
            });
    });
