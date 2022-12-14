<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 27 Nov 2022 12:48:35 Central Indonesia Time, Ubud, Bali, Indonesia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\FulfilmentOrder\CreateFulfilmentOrderFromIris;
use App\Actions\Inventory\Stock\CreateCustomerStockFromIris;
use App\Actions\Inventory\Stock\UpdateCustomerStockFromIris;

Route::post('/stocks', CreateCustomerStockFromIris::class)->name('create.stock');
Route::patch('/stocks', UpdateCustomerStockFromIris::class)->name('update.stock');

Route::post('/orders', CreateFulfilmentOrderFromIris::class)->name('create.order');
//Route::patch('/stocks', UpdateCustomerStockFromIris::class)->name('update.stock');
