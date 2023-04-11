<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 13:30:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

$catalogueRoutes = function () {
    require __DIR__.'/catalogue.php';
};
Route::middleware([
    "app",
])->group(function () use ($catalogueRoutes) {
    Route::middleware(["auth"])->group(function () use ($catalogueRoutes) {
        Route::get('/', function () {
            return redirect('/dashboard');
        });
        Route::prefix("dashboard")
            ->name("dashboard.")
            ->group(__DIR__."/dashboard.php");
        Route::prefix("account")
            ->name("account.")
            ->group(__DIR__."/account.php");
        Route::prefix("crm")
            ->name("crm.")
            ->group(__DIR__."/crm.php");
        Route::prefix("hr")
            ->name("hr.")
            ->group(__DIR__."/hr.php");
        Route::prefix("inventory")
            ->name("inventory.")
            ->group(__DIR__."/inventory.php");
        Route::prefix("fulfilment")
            ->name("fulfilment.")
            ->group(__DIR__."/fulfilment.php");
        Route::prefix("production")
            ->name("production.")
            ->group(__DIR__."/production.php");
        Route::prefix("procurement")
            ->name("procurement.")
            ->group(__DIR__."/procurement.php");
        Route::prefix("shops")
            ->name("shops.")
            ->group(__DIR__."/shops.php");
        Route::prefix("websites")
            ->name("websites.")
            ->group(__DIR__."/websites.php");
        Route::prefix("customers")
            ->name("customers.")
            ->group(__DIR__."/customers.php");
        Route::prefix("orders")
            ->name("orders.")
            ->group(__DIR__."/orders.php");
        Route::prefix("products")
            ->name("products.")
            ->group(__DIR__."/products.php");
        Route::prefix("search")
            ->name("search.")
            ->group(__DIR__."/search.php");
        Route::prefix("osm")
            ->name("osm.")
            ->group(__DIR__."/osm.php");
        Route::prefix("dispatch")
            ->name("dispatch.")
            ->group(__DIR__."/dispatch.php");
        Route::prefix("profile")
            ->name("profile.")
            ->group(__DIR__."/profile.php");
        Route::prefix("sysadmin")
            ->name("sysadmin.")
            ->group(__DIR__."/sysadmin.php");
        Route::prefix("accounting")
            ->name("accounting.")
            ->group(__DIR__."/accounting.php");
        Route::prefix("mail")
            ->name("mail.")
            ->group(__DIR__."/mail.php");
        Route::prefix("sessions")
            ->name("sessions.")
            ->group(__DIR__."/sessions.php");
        Route::prefix("models")
            ->name("models.")
            ->group(__DIR__."/models.php");
        Route::prefix("media")
            ->name("media.")
            ->group(__DIR__."/media.php");


        Route::prefix("catalogue")
            ->name("catalogue.")
            ->group(function () {
                $parent='tenant';
                require __DIR__.'/catalogue.php';
            });
    });

    require __DIR__."/auth.php";
});
