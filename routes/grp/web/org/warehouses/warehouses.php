<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Jan 2024 00:27:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\Warehouse\UI\CreateWarehouse;
use App\Actions\Inventory\Warehouse\UI\EditWarehouse;
use App\Actions\Inventory\Warehouse\UI\IndexWarehouses;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexWarehouses::class)->name('index');
Route::get('create', CreateWarehouse::class)->name('create');

Route::prefix('{warehouse}')
    ->group(function () {
        Route::get('locations', [IndexLocations::class, 'inWarehouse'])->name('locations.index');
        Route::get('edit', EditWarehouse::class)->name('edit');
        Route::name('show')
            ->group(function () {


                Route::prefix('inventory')->name('.inventory.')
                    ->group(__DIR__."/inventory.php");

                Route::prefix('incoming')->name('.incoming.')
                    ->group(__DIR__."/incoming.php");

                Route::prefix('dispatching')->name('.dispatching.')
                    ->group(__DIR__."/dispatching.php");

                Route::name('.infrastructure.')
                    ->group(__DIR__."/infrastructure.php");

                Route::prefix('fulfilment')->name('.fulfilment.')
                    ->group(__DIR__."/fulfilment.php");

            });
    });
