<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 20 Sept 2022 14:59:49 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('Central/Welcome', [

    ]);
})->name('.welcome');

require __DIR__.'/auth.php';



/*
Route::get('/', function () {
    return Inertia::render('Welcome', [

    ]);
})->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::prefix('hr')
        ->name('hr.')
        ->group(__DIR__.'/hr.php');
    Route::prefix('inventory')
        ->name('inventory.')
        ->group(__DIR__.'/inventory.php');

    Route::prefix('profile')
        ->name('profile.')
        ->group(__DIR__.'/profile.php');


    Route::prefix('sysadmin')
        ->name('sysadmin.')
        ->group(__DIR__.'/sysadmin.php');
});


Route::prefix('setup')->middleware(['auth', 'verified', 'verify.new'])->name('setup.')
    ->group(__DIR__.'/setup.php');

*/


