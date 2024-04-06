<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 29 Dec 2023 22:12:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Market\Shop\UI\CreateShop;
use App\Actions\Market\Shop\UI\IndexShops;
use Illuminate\Support\Facades\Route;

Route::get('', IndexShops::class)->name('index');
Route::get('create', CreateShop::class)->name('create');

Route::prefix('{shop}')->name('show.')
    ->group(function () {

        Route::name("catalogue.")
            ->group(__DIR__."/catalogue.php");
        Route::name("crm.")
            ->group(__DIR__."/crm.php");
        Route::name("orders.")
            ->group(__DIR__."/orders.php");

        Route::prefix("websites")
            ->name("web.websites.")
            ->group(__DIR__."/websites.php");
    });
