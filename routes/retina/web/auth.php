<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Sep 2023 12:10:17 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\CRM\WebUser\Retina\RetinaLogin;
use App\Actions\CRM\WebUser\Retina\LogoutRetina;
use App\Actions\CRM\WebUser\Retina\RetinaRegister;
use App\Actions\CRM\WebUser\Retina\UI\ShowRetinaLogin;
use App\Actions\CRM\WebUser\Retina\UI\ShowRetinaPrepareAccount;
use App\Actions\CRM\WebUser\Retina\UI\ShowRetinaRegister;
use App\Actions\CRM\WebUser\Retina\UI\ShowRetinaResetWebUserPassword;
use App\Actions\CRM\WebUser\Retina\UpdateRetinaWebUserPassword;
use App\Actions\CRM\WebUser\UpdateRetinaWebUserPasswordViaEmail;
use App\Actions\UI\Retina\Auth\PasswordRetinaResetLink;
use App\Actions\UI\Retina\Auth\ShowRetinaPasswordResetLink;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:retina')->group(function () {
    Route::get('login', ShowRetinaLogin::class)->name('login.show');
    Route::post('login', RetinaLogin::class)->name('login.store');
    Route::get('register', ShowRetinaRegister::class)->name('register');
    Route::post('register', RetinaRegister::class)->name('register.store');

    Route::get('email-reset-password', ShowRetinaPasswordResetLink::class)->name('email.reset-password.edit');
    Route::get('reset-password', ShowRetinaResetWebUserPassword::class)->name('email.reset-password.show');
    Route::post('reset/password/link', PasswordRetinaResetLink::class)->name('password.email');
    Route::patch('reset/password/email', UpdateRetinaWebUserPasswordViaEmail::class)->name('reset-password.email.update');
});

Route::middleware('retina-auth:retina')->group(function () {
    Route::post('logout', LogoutRetina::class)->name('logout');
    Route::get('reset/password', ShowRetinaResetWebUserPassword::class)->name('reset-password.edit');
    Route::patch('reset/password', UpdateRetinaWebUserPassword::class)->name('reset-password.update');
    Route::get('prepare-account', ShowRetinaPrepareAccount::class)->name('prepare-account.show');
});
