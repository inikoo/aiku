<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 10 Aug 2022 15:52:49 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

use App\Actions\Auth\UI\AuthSession\Login;
use App\Actions\Auth\UI\AuthSession\Logout;
use App\Actions\Auth\UI\AuthSession\ShowLogin;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', ShowLogin::class)->name('login');
    Route::post('login', Login::class);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', Logout::class)->name('logout');
});
