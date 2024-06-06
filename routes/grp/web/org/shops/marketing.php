<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 11:23:04 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Devel\UI\CreateDummy;
use App\Actions\Devel\UI\EditDummy;
use App\Actions\Devel\UI\IndexDummies;
use App\Actions\Devel\UI\ShowDummy;
use App\Actions\UI\Dropshipping\Marketing\ShowMarketingDashboard;
use Illuminate\Support\Facades\Route;

Route::get('', ShowMarketingDashboard::class)->name('dashboard');
Route::name("newsletters.")->prefix('newsletters')
    ->group(function () {
        Route::get('', IndexDummies::class)->name('index');
        Route::get('create', CreateDummy::class)->name('create');
        Route::get('{mailshot}', ShowDummy::class)->name('show');
        Route::get('{mailshot}/edit', EditDummy::class)->name('edit');
    });
Route::name("mailshots.")->prefix('mailshots')
    ->group(function () {
        Route::get('', IndexDummies::class)->name('index');
        Route::get('create', CreateDummy::class)->name('create');
        Route::get('{mailshot}', ShowDummy::class)->name('show');
        Route::get('{mailshot}/edit', EditDummy::class)->name('edit');
    });
Route::name("notifications.")->prefix('notifications')
    ->group(function () {
        Route::get('', IndexDummies::class)->name('index');
        Route::get('create', CreateDummy::class)->name('create');
        Route::get('{mailshot}', ShowDummy::class)->name('show');
        Route::get('{mailshot}/edit', EditDummy::class)->name('edit');
    });
