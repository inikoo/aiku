<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 10 Aug 2022 15:52:49 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\FacebookAuthController;
use Illuminate\Support\Facades\Route;



Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('central.register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('central.login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('central.login.store');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('central.password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('central.password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('central.password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('central.password.update');


    Route::get('auth/facebook', [FacebookAuthController::class, 'facebookRedirect'])->name('central.facebook.store');;
    Route::get('auth/facebook/callback', [FacebookAuthController::class, 'loginWithFacebook'])->name('central.facebook.callback');;

});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])
                ->name('central.verification.notice');

    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('central.verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('central.verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->name('central.password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])->name('central.password.confirm.store');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('central.logout');
});

