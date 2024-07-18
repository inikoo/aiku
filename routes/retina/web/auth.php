<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Sep 2023 12:10:17 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\CRM\WebUser\Retina\Login;
use App\Actions\CRM\WebUser\Retina\Logout;
use App\Actions\CRM\WebUser\Retina\Register;
use App\Actions\CRM\WebUser\Retina\UI\ShowLogin;
use App\Actions\CRM\WebUser\Retina\UI\ShowPrepareAccount;
use App\Actions\CRM\WebUser\Retina\UI\ShowRegister;
use App\Actions\CRM\WebUser\Retina\UI\ShowResetWebUserPassword;
use App\Actions\CRM\WebUser\Retina\UpdateWebUserPassword;
use App\Actions\CRM\WebUser\UpdateWebUserPasswordViaEmail;
use App\Actions\UI\Retina\Auth\PasswordResetLink;
use App\Actions\UI\Retina\Auth\ShowPasswordResetLink;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:retina')->group(function () {
    Route::get('login', ShowLogin::class)->name('login.show');
    Route::post('login', Login::class)->name('login.store');
    Route::get('register', ShowRegister::class)->name('register');
    Route::post('register', Register::class)->name('register.store');

    Route::get('email-reset-password', ShowPasswordResetLink::class)->name('email.reset-password.edit');
    Route::get('reset-password', ShowResetWebUserPassword::class)->name('email.reset-password.show');
    Route::post('reset/password/link', PasswordResetLink::class)->name('password.email');
    Route::patch('reset/password/email', UpdateWebUserPasswordViaEmail::class)->name('reset-password.email.update');
});

Route::middleware('retina-auth:retina')->group(function () {
    Route::post('logout', Logout::class)->name('logout');
    Route::get('reset/password', ShowResetWebUserPassword::class)->name('reset-password.edit');
    Route::patch('reset/password', UpdateWebUserPassword::class)->name('reset-password.update');

    Route::get('prepare-account', ShowPrepareAccount::class)->name('prepare-account.show');
});
