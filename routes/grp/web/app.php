<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 13:30:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\UI\Grp\EditGroup;
use App\Actions\UI\Grp\IndexGroups;
use App\Actions\UI\Grp\ShowGroup;
use App\Actions\UI\Notification\IndexNotification;
use Illuminate\Support\Facades\Route;

Route::middleware(["auth"])->group(function () {
    Route::get('/', function () {
        return redirect('/dashboard');
    });
    Route::get('/groups', IndexGroups::class)->name('index');
    Route::get('/group', ShowGroup::class)->name('show');
    Route::get('/group/edit', EditGroup::class)->name('edit');

    Route::get('/notifications', IndexNotification::class)->name('notifications');
    Route::prefix("overview")
        ->name("overview.")
        ->group(__DIR__."/overview.php");
    Route::prefix("organisations")
        ->name("organisations.")
        ->group(__DIR__."/organisations.php");
    Route::prefix("dashboard")
        ->name("dashboard.")
        ->group(__DIR__."/dashboard.php");
    Route::prefix("supply-chain")
        ->name("supply-chain.")
        ->group(__DIR__."/supply-chain.php");
    Route::prefix("goods")
        ->name("goods.")
        ->group(__DIR__."/goods.php");
    Route::prefix("profile")
        ->name("profile.")
        ->group(__DIR__."/profile.php");
    Route::prefix("sysadmin")
        ->name("sysadmin.")
        ->group(__DIR__."/sysadmin.php");
    Route::prefix("org/{organisation}")
        ->name("org.")
        ->group(__DIR__."/org/org.php");
    Route::prefix("models")
        ->name("models.")
        ->group(__DIR__."/models.php");
    Route::prefix("search")
        ->name("search.")
        ->group(__DIR__."/search.php");
    Route::prefix("media")
        ->name("media.")
        ->group(__DIR__."/media.php");
    Route::prefix("gallery")
        ->name("gallery.")
        ->group(__DIR__."/gallery.php");

});
require __DIR__."/auth.php";
