<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 29 Dec 2023 22:12:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\SysAdmin\Organisation\UI\ShowOrganisation;
use Illuminate\Support\Facades\Route;

Route::prefix("")
    ->name("dashboard.")
    ->group(__DIR__."/dashboard.php");

Route::prefix("hr")
    ->name("hr.")
    ->group(__DIR__."/hr.php");

Route::prefix("shops")
    ->name("shops.")
    ->group(__DIR__."/shops/shop.php");

Route::prefix("websites")
    ->name("websites.")
    ->group(__DIR__."/websites/website.php");


Route::prefix("fulfilments")
    ->name("fulfilments.")
    ->group(__DIR__."/fulfilments/root.php");

Route::prefix("inventory")
    ->name("inventory.")
    ->group(__DIR__."/inventory/inventory.php");

Route::prefix("warehouses")
    ->name("warehouses.")
    ->group(__DIR__."/warehouses/warehouses.php");

Route::prefix("factory")
    ->name("productions.")
    ->group(__DIR__."/manufacturing/productions.php");

Route::prefix("procurement")
    ->name("procurement.")
    ->group(__DIR__."/procurement.php");

Route::prefix("accounting")
    ->name("accounting.")
    ->group(__DIR__."/accounting.php");

Route::prefix("reports")
    ->name("reports.")
    ->group(__DIR__."/reports.php");

Route::prefix("settings")
    ->name("settings.")
    ->group(__DIR__."/settings.php");


Route::get('/show', ShowOrganisation::class)->name('show');
