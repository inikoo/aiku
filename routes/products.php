<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 08:28:40 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Marketing\Product\IndexProducts;
use App\Actions\Marketing\Product\ShowProduct;
use Illuminate\Support\Facades\Route;


Route::get('/', IndexProducts::class)->name('index');
Route::get('/{product}', ShowProduct::class)->name('show');

