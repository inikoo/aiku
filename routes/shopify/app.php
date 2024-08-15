<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 15 Aug 2024 08:55:48 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use Osiset\ShopifyApp\Http\Controllers\AuthController;

Route::middleware(['verify.shopify'])->group(function () {
    Route::get('', function () {
        return view('shopify.index');
    })->name('home');
});

Route::match(
    ['GET', 'POST'],
    '/authenticate',
    [AuthController::class, 'authenticate']
)->name('authenticate');

Route::get(
    '/authenticate/token',
    [AuthController::class, 'token']
)->name('authenticate.token');
