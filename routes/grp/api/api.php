<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 28 Dec 2023 15:37:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\HumanResources\Clocking\StoreClocking;
use App\Actions\HumanResources\ClockingMachine\UI\ShowClockingMachine;
use App\Actions\HumanResources\Employee\UI\ShowEmployee;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::middleware(['auth:sanctum','bind_group'])->group(function () {
        Route::prefix("profile")
            ->name("profile.")
            ->group(__DIR__."/profile.php");

        require __DIR__."/org.php";

    });
    require __DIR__."/tokens.php";

    // This is not complete yet, only for testing if its works
    Route::prefix('clocking')->as('clocking.')->group(function () {
        Route::get('clocking-machine/{qr}', [ShowClockingMachine::class, 'inApi'])->name('clocking-machine.qr.show');
        Route::post('employees/pin', [ShowEmployee::class, 'inApi'])->name('employees.pin.show');
        Route::post('{clockingMachine:id}/clocking-machine/{employee:id}/employees', [StoreClocking::class, 'inApi'])
            ->name('clocking-machine.employee.store')->withoutScopedBindings();
    });
});
