<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 05 Feb 2024 11:24:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

Route::middleware(["retina-auth:retina"])->group(function () {
    Route::get('/', function () {
        return redirect('/app/dashboard');
    })->name('home');

    Route::middleware(["retina-reset-pass"])->group(function () {
        Route::prefix("dashboard")
            ->name("dashboard.")
            ->group(__DIR__."/dashboard.php");
    });
});
require __DIR__."/auth.php";
