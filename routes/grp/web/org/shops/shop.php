<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 29 Dec 2023 22:12:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Shop\UI\CreateShop;
use App\Actions\Catalogue\Shop\UI\EditShop;
use App\Actions\Catalogue\Shop\UI\IndexShops;
use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\Catalogue\Shop\UI\ShowShopDashboard;
use App\Stubs\UIDummies\ShowDummyDashboard;
use Illuminate\Support\Facades\Route;

Route::get('', IndexShops::class)->name('index');
Route::get('create', CreateShop::class)->name('create');
Route::get('{shop}', ShowShop::class)->name('show');
Route::get('{shop}/edit', EditShop::class)->name('edit');

Route::prefix('{shop}')->name('show.')
    ->group(function () {
        Route::get('dashboard', ShowShopDashboard::class)->name('dashboard');
        Route::name("catalogue.")->prefix('catalogue')
            ->group(__DIR__."/catalogue.php");

        Route::name("assets.")->prefix('assets')
            ->group(__DIR__."/assets.php");

        Route::name("mail.")->prefix('mail')
            ->group(__DIR__."/mails.php");

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

        Route::prefix("web")
            ->name("web.")
            ->group(__DIR__."/websites.php");

        Route::prefix("setting")
            ->name("setting.")
            ->group(__DIR__."/settings.php");
    });
