<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 11 Aug 2022 22:50:46 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('Welcome', [

    ]);
})->name('welcome');

Route::middleware(['auth', 'verified', 'set.organisation'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::prefix('hr')
        ->name('hr.')
        ->group(__DIR__.'/hr.php');

    Route::prefix('sysadmin')
        ->name('sysadmin.')
        ->group(__DIR__.'/sysadmin.php');
});


Route::prefix('setup')->middleware(['auth', 'verified', 'verify.new'])->name('setup.')
    ->group(__DIR__.'/setup.php');


require __DIR__.'/auth.php';


