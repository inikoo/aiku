<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 05 Feb 2024 11:24:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

Route::middleware(["retina-auth:retina", 'retina-prepare-account'])->group(function () {
    Route::get('/', function () {
        return redirect('/app/dashboard');
    })->name('home');

    Route::prefix("sysadmin")
        ->name("sysadmin.")
        ->group(__DIR__."/sysadmin.php");

    Route::prefix("billing")
        ->name("billing.")
        ->group(__DIR__."/billing.php");


    Route::prefix("storage")
        ->name("storage.")
        ->group(__DIR__."/storage.php");

    Route::prefix("models")
        ->name("models.")
        ->group(__DIR__."/models.php");

    Route::prefix("search")
        ->name("search.")
        ->group(__DIR__."/search.php");

    Route::prefix("json")
        ->name("json.")
        ->group(__DIR__."/json.php");

    Route::middleware(["retina-reset-pass"])->group(function () {
        Route::prefix("dashboard")
            ->name("dashboard.")
            ->group(__DIR__."/dashboard.php");
        Route::prefix("profile")
            ->name("profile.")
            ->group(__DIR__."/profile.php");
    });
});
require __DIR__."/auth.php";
