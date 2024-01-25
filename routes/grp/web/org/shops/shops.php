<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 29 Dec 2023 22:12:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Market\Shop\UI\CreateShop;
use App\Actions\Market\Shop\UI\EditShop;
use App\Actions\Market\Shop\UI\IndexShops;
use App\Actions\Market\Shop\UI\RemoveShop;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Actions\Web\Website\UI\CreateWebsite;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexShops::class)->name('index');
Route::get('create', CreateShop::class)->name('create');

Route::prefix('{shop}')
    ->group(function () {
        Route::get('', ShowShop::class)->name('show');
        Route::get('edit', EditShop::class)->name('edit');
        Route::get('delete', RemoveShop::class)->name('remove');
        Route::get('website/create', [CreateWebsite::class, 'inShop'])->name('show.website.create');

        Route::prefix("crm")
            ->name("crm.")
            ->group(__DIR__."/crm.php");

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

Route::prefix("shops")
    ->name("shops.")
    ->group(__DIR__."/shops.php");
Route::prefix("web")
    ->name("web.")
    ->group(__DIR__."/web.php");
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
