<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Jan 2024 00:27:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Inventory\Warehouse\UI\CreateWarehouse;
use App\Actions\Inventory\Warehouse\UI\EditWarehouse;
use App\Actions\Inventory\Warehouse\UI\IndexWarehouses;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexWarehouses::class)->name('index');
Route::get('create', CreateWarehouse::class)->name('create');

Route::prefix('{warehouse}')
    ->group(function () {
        Route::get('', ShowWarehouse::class)->name('show');
        Route::get('edit', EditWarehouse::class)->name('edit');




    });
