<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 15:46:00 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Shop\Api\IndexDropshippingShops;
use App\Actions\Catalogue\Shop\Api\ShowDropshippingShop;
use App\Actions\Dropshipping\ConnectToDroppings;
use Illuminate\Support\Facades\Route;

Route::name('dropshipping.')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {

        Route::get('shops', IndexDropshippingShops::class)->name('shops.index');
        Route::get('shops/{shop:id}', ShowDropshippingShop::class)->name('shops.show');


    });

    Route::post('connect', ConnectToDroppings::class)->name('connect');

});
