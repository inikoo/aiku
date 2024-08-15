<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 15 Aug 2024 08:55:48 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Dropshipping\Shopify\GetProductForShopify;
use Osiset\ShopifyApp\Http\Controllers\AuthController;
use App\Actions\UI\Shopify\Dashboard\ShowDashboard;

Route::middleware(['verify.shopify'])->group(function () {
    // Route::get('', function () {
    //     return view('shopify.index');
    // })->name('home');
    Route::get('/', ShowDashboard::class)->name('home');
});



Route::get('/shopifytest', ShowDashboard::class)->name('home');

Route::get('/products', GetProductForShopify::class)->name('products');

Route::match(
    ['GET', 'POST'],
    '/authenticate',
    [AuthController::class, 'authenticate']
)->name('authenticate');

Route::get(
    '/authenticate/token',
    [AuthController::class, 'token']
)->name('authenticate.token');
