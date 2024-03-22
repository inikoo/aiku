<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 10 Aug 2022 15:52:49 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

use App\Actions\SysAdmin\UI\Grp\Login;
use App\Actions\SysAdmin\UI\Grp\Logout;
use App\Actions\SysAdmin\UI\Grp\ShowLogin;
use App\Actions\SysAdmin\UI\Grp\ShowResetPassword;
use App\Actions\SysAdmin\User\UI\ShowResetUserPassword;
use App\Actions\SysAdmin\User\UpdateUserPassword;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('resetpassword', ShowResetPassword::class)->name('reset.password');
    Route::get('login', ShowLogin::class)->name('login.show');
    Route::post('login', Login::class)->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', Logout::class)->name('logout');
    Route::get('reset/password', ShowResetUserPassword::class)->name('reset-password.edit');
    Route::patch('reset/password', UpdateUserPassword::class)->name('reset-password.update');

});
