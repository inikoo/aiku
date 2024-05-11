<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 29 Dec 2023 22:12:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Devel\UI\ShowDummyDashboard;
use App\Actions\Catalogue\Shop\UI\CreateShop;
use App\Actions\Catalogue\Shop\UI\IndexShops;
use App\Actions\Catalogue\Shop\UI\ShowShop;
use Illuminate\Support\Facades\Route;

Route::get('', IndexShops::class)->name('index');
Route::get('create', CreateShop::class)->name('create');

Route::get('{shop}', ShowShop::class)->name('show');

Route::prefix('{shop}')->name('show.')
    ->group(function () {

        Route::name("catalogue.")->prefix('catalogue')
            ->group(__DIR__."/catalogue.php");


        Route::name("crm.")->prefix('crm')->group(
            function () {
                Route::get('', ShowDummyDashboard::class)->name('dashboard');
                Route::prefix("customers")
                    ->name("customers.")
                    ->group(__DIR__."/customers.php");
                Route::prefix("prospects")
                    ->name("prospects.")
                    ->group(__DIR__."/prospects.php");
            }
        );


        Route::name("ordering.")->prefix('ordering')
            ->group(__DIR__."/ordering.php");

        Route::name("offers.")->prefix('offers')
            ->group(__DIR__."/offers.php");

        Route::name("marketing.")->prefix('marketing')
            ->group(__DIR__."/marketing.php");

        Route::prefix("websites")
            ->name("web.websites.")
            ->group(__DIR__."/websites.php");
    });
