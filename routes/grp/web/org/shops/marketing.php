<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 11:23:04 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Mail\Mailshot\UI\CreateMailshot;
use App\Actions\Mail\Mailshot\UI\EditMailshot;
use App\Actions\Mail\Mailshot\UI\IndexMailshots;
use App\Actions\Mail\Mailshot\UI\ShowMailshot;
use App\Actions\UI\Dropshipping\Marketing\ShowMarketingDashboard;
use App\Stubs\UIDummies\CreateDummy;
use App\Stubs\UIDummies\EditDummy;
use App\Stubs\UIDummies\IndexDummies;
use App\Stubs\UIDummies\ShowDummy;
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
        Route::get('', [IndexMailshots::class, 'inShop'])->name('index');
        Route::get('create', CreateMailshot::class)->name('create');
        Route::get('{mailshot}', [ShowMailshot::class, 'inShop'])->name('show');
        Route::get('{mailshot}/edit', EditMailshot::class)->name('edit');
    });
Route::name("notifications.")->prefix('notifications')
    ->group(function () {
        Route::get('', IndexDummies::class)->name('index');
        Route::get('create', CreateDummy::class)->name('create');
        Route::get('{mailshot}', ShowDummy::class)->name('show');
        Route::get('{mailshot}/edit', EditDummy::class)->name('edit');
    });
