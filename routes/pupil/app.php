<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 15 Aug 2024 08:55:48 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Dropshipping\Shopify\Product\GetProductForShopify;
use App\Actions\Dropshipping\Shopify\Product\StoreProductShopify;
use App\Actions\UI\Pupil\Dashboard\ShowDashboard;
use Osiset\ShopifyApp\Http\Controllers\AuthController;

Route::middleware(['verify.shopify'])->group(function () {
    Route::get('/', ShowDashboard::class)->name('home');
    Route::get('shopify-user/{shopifyUser:id}/products', GetProductForShopify::class)->name('products');
    Route::post('shopify-user/{shopifyUser:id}/products', StoreProductShopify::class)->name('shopify_user.product.store')->withoutScopedBindings();
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
