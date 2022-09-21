<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    Route::middleware(['auth'])->group(function () {
        Route::get('/', function () {
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
    require __DIR__.'/auth.php';

});
