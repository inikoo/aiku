<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 21 Sep 2022  Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    "web",
    InitializeTenancyBySubdomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::middleware(["auth"])->group(function () {

        Route::prefix("dashboard")
            ->name("dashboard.")
            ->group(__DIR__ . "/dashboard.php");

        Route::prefix("hr")
            ->name("hr.")
            ->group(__DIR__ . "/hr.php");
        Route::prefix("inventory")
            ->name("inventory.")
            ->group(__DIR__ . "/inventory.php");
        Route::prefix("fulfilment")
            ->name("fulfilment.")
            ->group(__DIR__ . "/fulfilment.php");
        Route::prefix("production")
            ->name("production.")
            ->group(__DIR__ . "/production.php");
        Route::prefix("procurement")
            ->name("procurement.")
            ->group(__DIR__ . "/procurement.php");
        Route::prefix("shops")
            ->name("shops.")
            ->group(__DIR__ . "/shops.php");
        Route::prefix("websites")
            ->name("websites.")
            ->group(__DIR__ . "/websites.php");
        Route::prefix("customers")
            ->name("customers.")
            ->group(__DIR__ . "/customers.php");
        Route::prefix("profile")
            ->name("profile.")
            ->group(__DIR__ . "/profile.php");

        Route::prefix("sysadmin")
            ->name("sysadmin.")
            ->group(__DIR__ . "/sysadmin.php");
    });
    require __DIR__ . "/auth.php";
});
