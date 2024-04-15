<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 15:07:52 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentProducts;
use App\Actions\Market\Product\UI\ShowProduct;

Route::get('products', IndexFulfilmentProducts::class)->name('index');
Route::get('products/{product}', ShowProduct::class)->name('show');

Route::get('rentals', IndexFulfilmentProducts::class)->name('rentals.index');
Route::get('services', IndexFulfilmentProducts::class)->name('services.index');
Route::get('goods', IndexFulfilmentProducts::class)->name('goods.index');
