<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 28 Dec 2023 15:37:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\HumanResources\Clocking\StoreClocking;
use App\Actions\HumanResources\Clocking\UI\GetEmployeeUsingPin;
use App\Actions\HumanResources\ClockingMachine\UI\ShowClockingMachine;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::middleware(['auth:sanctum','bind_group'])->group(function () {
        Route::prefix("profile")
            ->name("profile.")
            ->group(__DIR__."/profile.php");

        require __DIR__."/org.php";

    });
    require __DIR__."/tokens.php";
    Route::post('clocking/pin', GetEmployeeUsingPin::class)->name('clocking.pin');
    Route::get('clocking/clocking-machine/{qr}', [ShowClockingMachine::class, 'inApi'])->name('clocking.qr');
    Route::post('clocking/store', [StoreClocking::class, 'inApi'])->name('clocking.store');
});
