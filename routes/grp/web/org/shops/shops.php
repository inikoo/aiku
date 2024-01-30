<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 29 Dec 2023 22:12:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Market\Shop\UI\CreateShop;
use App\Actions\Market\Shop\UI\IndexShops;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexShops::class)->name('index');
Route::get('create', CreateShop::class)->name('create');

Route::prefix('{shop}')
    ->group(function () {

        Route::name('show.')
            ->group(function () {
                Route::name("catalogue.")
                    ->group(__DIR__."/catalogue.php");
                Route::name("crm.")
                    ->group(__DIR__."/crm.php");

                Route::prefix("websites")
                    ->name("web.websites.")
                    ->group(__DIR__."/websites.php");
            });
    });


/*



Route::prefix("account")
    ->name("account.")
    ->group(__DIR__."/account.php");
Route::prefix("bi")
    ->name("business_intelligence.")
    ->group(__DIR__."/business_intelligence.php");

Route::prefix("hr")
    ->name("hr.")
    ->group(__DIR__."/hr.php");
Route::prefix("inventory")
    ->name("inventory.")
    ->group(__DIR__."/warehouses.php");
Route::prefix("fulfilment")
    ->name("fulfilment.")
    ->group(__DIR__."/fulfilment.php");
Route::prefix("dropshipping")
    ->name("dropshipping.")
    ->group(__DIR__."/dropshipping.php");
Route::prefix("production")
    ->name("production.")
    ->group(__DIR__."/production.php");



Route::prefix("products")
    ->name("products.")
    ->group(__DIR__."/products.php");
Route::prefix("search")
    ->name("search.")
    ->group(__DIR__."/search.php");
Route::prefix("oms")
    ->name("oms.")
    ->group(__DIR__."/oms.php");
Route::prefix("dispatch")
    ->name("dispatch.")
    ->group(__DIR__."/dispatch.php");

Route::prefix("accounting")
    ->name("accounting.")
    ->group(__DIR__."/accounting.php");
Route::prefix("marketing")
    ->name("marketing.")
    ->group(__DIR__."/marketing.php");
Route::prefix("sessions")
    ->name("sessions.")
    ->group(__DIR__."/sessions.php");

Route::prefix("media")
    ->name("media.")
    ->group(__DIR__."/media.php");
Route::prefix("json")
    ->name("json.")
    ->group(__DIR__."/json.php");
Route::prefix("google")
    ->name("google.")
    ->group(__DIR__."/google.php");

*/
