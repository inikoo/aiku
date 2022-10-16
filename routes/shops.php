<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 16:27:58 Central European Summer Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Marketing\SHop\IndexShops;
use App\Actions\Marketing\Shop\ShowShop;


Route::get('/', IndexShops::class)->name('index');
Route::get('/{shop}', ShowShop::class)->name('show');
