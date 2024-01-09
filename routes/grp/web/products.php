<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 08:28:40 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Market\Product\UI\CreateProduct;
use App\Actions\Market\Product\UI\EditProduct;
use App\Actions\Market\Product\UI\IndexProducts;
use App\Actions\Market\Product\UI\ShowProduct;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexProducts::class)->name('index');
Route::get('/create', CreateProduct::class)->name('create');
Route::get('/{product}', ShowProduct::class)->name('show');
Route::get('/{product}/edit', EditProduct::class)->name('edit');
