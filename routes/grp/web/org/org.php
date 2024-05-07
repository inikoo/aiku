<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 29 Dec 2023 22:12:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Support\Facades\Route;

Route::prefix("")
    ->name("dashboard.")
    ->group(__DIR__."/dashboard.php");

Route::prefix("hr")
    ->name("hr.")
    ->group(__DIR__."/hr.php");

Route::prefix("shops")
    ->name("shops.")
    ->group(__DIR__."/shops/root.php");

Route::prefix("fulfilments")
    ->name("fulfilments.")
    ->group(__DIR__."/fulfilments/root.php");

Route::prefix("inventory")
    ->name("inventory.")
    ->group(__DIR__."/inventory/inventory.php");

Route::prefix("warehouses")
    ->name("warehouses.")
    ->group(__DIR__."/warehouses/warehouses.php");

Route::prefix("procurement")
    ->name("procurement.")
    ->group(__DIR__."/procurement.php");

Route::prefix("accounting")
    ->name("accounting.")
    ->group(__DIR__."/accounting.php");

Route::prefix("reports")
    ->name("reports.")
    ->group(__DIR__."/reports.php");

Route::prefix("dispatch")
    ->name("dispatch.")
    ->group(__DIR__ . "/dispatch.php");

Route::prefix("manufacturing")
    ->name("manufacturing.")
    ->group(__DIR__."/manufacturing/manufacturing.php");

/*

Route::prefix("crm")
    ->name("crm.")
    ->group(__DIR__."/crm.php");

Route::prefix("account")
    ->name("account.")
    ->group(__DIR__."/account.php");



Route::prefix("dropshipping")
    ->name("dropshipping.")
    ->group(__DIR__."/dropshipping.php");


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
    ->group(__DIR__."/orders.php");



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
