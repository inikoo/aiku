<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 15 Aug 2024 08:55:48 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Dropshipping\Shopify\DeleteProductFromShopify;
use App\Actions\Dropshipping\Shopify\GetProductForShopify;
use App\Actions\Dropshipping\Shopify\StoreProductToShopify;
use App\Stubs\UIDummies\CreateDummy;
use Osiset\ShopifyApp\Http\Controllers\AuthController;
use App\Actions\UI\Pupil\Dashboard\ShowDashboard;

Route::middleware(['verify.shopify'])->group(function () {
    Route::get('/', ShowDashboard::class)->name('home');
    Route::get('shopify-user/{shopifyUser:id}/products', GetProductForShopify::class)->name('products');
    Route::post('shopify-user/{shopifyUser:id}/products', StoreProductToShopify::class)->name('shopify_user.product.store')->withoutScopedBindings();
});

Route::post('/webhooks/products/delete', DeleteProductFromShopify::class)->name('webhooks.products.delete');
Route::post('/webhooks/orders/delete', CreateDummy::class)->name('webhooks.orders.store');

Route::match(
    ['GET', 'POST'],
    '/authenticate',
    [AuthController::class, 'authenticate']
)->name('authenticate');

Route::get(
    '/authenticate/token',
    [AuthController::class, 'token']
)->name('authenticate.token');
