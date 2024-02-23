<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Sep 2023 12:10:17 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\CRM\WebUser\Retina\Login;
use App\Actions\CRM\WebUser\Retina\Logout;
use App\Actions\CRM\WebUser\Retina\UI\ShowLogin;
use App\Actions\CRM\WebUser\Retina\UI\ShowRegister;
use App\Actions\CRM\WebUser\Retina\UI\ShowResetWebUserPassword;
use App\Actions\CRM\WebUser\Retina\UpdateWebUserPassword;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:retina')->group(function () {
    Route::get('login', ShowLogin::class)->name('login.show');
    Route::post('login', Login::class)->name('login.store');
    Route::get('register', ShowRegister::class)->name('register');
    // Route::post('register', Register::class);

});

Route::middleware('retina-auth:retina')->group(function () {
    Route::post('logout', Logout::class)->name('logout');
    Route::get('reset/password', ShowResetWebUserPassword::class)->name('reset-password.edit');
    Route::patch('reset/password', UpdateWebUserPassword::class)->name('reset-password.update');

});
