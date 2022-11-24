<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 17 Nov 2022 12:19:04 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Inventory\Stock\CreateCustomerStockFromIris;
use App\Actions\Inventory\Stock\UpdateCustomerStockFromIris;

Route::post('/stocks', CreateCustomerStockFromIris::class)->name('create.stock');
Route::patch('/stocks', UpdateCustomerStockFromIris::class)->name('update.stock');
