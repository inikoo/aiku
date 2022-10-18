<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 13 Oct 2022 15:46:44 Central European Summer Time, Plane Malaga - East Midlands UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Marketing\SHop\IndexShops;
use App\Actions\Marketing\Shop\ShowShop;


Route::get('/', IndexShops::class)->name('index');
Route::get('/{website}', ShowShop::class)->name('show');
