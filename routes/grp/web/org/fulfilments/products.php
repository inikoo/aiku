<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 15:07:52 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Service\UI\CreateService;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentPhysicalGoods;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentProducts;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentRentals;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentServices;
use App\Actions\Fulfilment\Rental\UI\CreateRental;
use App\Actions\Catalogue\Product\UI\ShowProduct;

Route::get('products', IndexFulfilmentProducts::class)->name('index');
Route::get('products/{product}', [ShowProduct::class, 'inFulfilment'])->name('show');

Route::get('rentals', IndexFulfilmentRentals::class)->name('rentals.index');
Route::get('services', IndexFulfilmentProducts::class)->name('services.index');
Route::get('goods', IndexFulfilmentProducts::class)->name('goods.index');

Route::get('rentals/create', CreateRental::class)->name('rentals.create');

Route::get('services', IndexFulfilmentServices::class)->name('services.index');
Route::get('services/create', CreateService::class)->name('services.create');


Route::get('physical-goods', IndexFulfilmentPhysicalGoods::class)->name('outers.index');
