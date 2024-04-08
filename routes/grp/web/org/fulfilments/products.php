<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 15:07:52 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentProducts;

Route::get('products', IndexFulfilmentProducts::class)->name('index');
Route::get('products/rent', IndexFulfilmentProducts::class)->name('rent.index');
Route::get('products/services', IndexFulfilmentProducts::class)->name('services.index');
Route::get('products/goods', IndexFulfilmentProducts::class)->name('goods.index');
