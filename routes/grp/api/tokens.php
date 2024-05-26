<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 28 Dec 2023 15:39:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\HumanResources\ClockingMachine\DisconnectClockingMachineFromHan;
use App\Actions\HumanResources\ClockingMachine\ConnectClockingMachineToHan;
use App\Actions\SysAdmin\User\UI\StoreUserApiTokenFromCredentials;
use App\Actions\SysAdmin\User\UI\StoreUserApiTokenFromQRCode;
use App\Actions\SysAdmin\User\UpdateFcmTokenUser;
use Illuminate\Support\Facades\Route;

Route::name('mobile-app.tokens.')->group(function () {
    Route::post('tokens/qr-code', StoreUserApiTokenFromQRCode::class)->name('qr-code.store');
    Route::post('tokens/credentials', StoreUserApiTokenFromCredentials::class)->name('credentials.store');

    Route::prefix('clocking-machines')->name('clocking-machine.')->group(function () {
        Route::post('tokens/qr-code', ConnectClockingMachineToHan::class)->name('qr-code.store');
        Route::delete('tokens/qr-code', DisconnectClockingMachineFromHan::class)->name('qr-code.delete')->middleware(['auth:sanctum', 'bind_group']);
    });
});

Route::name('firebase-token.')->prefix('firebase-token')->middleware(['auth:sanctum','bind_group'])->group(function () {
    Route::patch('', UpdateFcmTokenUser::class)->name('fcm.update');
});
