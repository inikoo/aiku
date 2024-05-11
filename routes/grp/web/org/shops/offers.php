<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jan 2024 11:31:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Devel\UI\CreateDummy;
use App\Actions\Devel\UI\EditDummy;
use App\Actions\Devel\UI\IndexDummies;
use App\Actions\Devel\UI\ShowDummy;
use App\Actions\Devel\UI\ShowDummyDashboard;
use Illuminate\Support\Facades\Route;

Route::get('', ShowDummyDashboard::class)->name('dashboard');
Route::name("campaigns.")->prefix('campaigns')
    ->group(function () {
        Route::get('', IndexDummies::class)->name('index');
        Route::get('create', CreateDummy::class)->name('create');
        Route::get('{marketingCampaign}', ShowDummy::class)->name('show');
        Route::get('{marketingCampaign}/edit', EditDummy::class)->name('edit');
    });

Route::name("offers.")->prefix('offers')
    ->group(function () {
        Route::get('', IndexDummies::class)->name('index');
        Route::get('create', CreateDummy::class)->name('create');
        Route::get('{offer}', ShowDummy::class)->name('show');
        Route::get('{offer}/edit', EditDummy::class)->name('edit');
    });
