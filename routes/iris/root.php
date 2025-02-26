<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Feb 2024 16:50:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */



use App\Actions\UI\Iris\ShowHome;
use App\Actions\Web\Banner\UI\DeliverBanner;
use Illuminate\Support\Facades\Route;

Route::middleware(["iris-auth:retina"])->group(function () {
    Route::prefix("")->group(function () {
        require __DIR__ . '/system.php';

        Route::get('/{path?}', ShowHome::class)->where("path", ".*")->name('home');
    });
});

Route::prefix("crm")
    ->name("crm.")
    ->group(__DIR__."/crm.php");

Route::prefix("disclosure")
    ->name("disclosure.")
    ->group(__DIR__."/disclosure.php");

Route::prefix("unsubscribe")
    ->name("unsubscribe.")
    ->group(__DIR__."/unsubscribe.php");

Route::get('/banners/{slug}', DeliverBanner::class)->name('banner');
