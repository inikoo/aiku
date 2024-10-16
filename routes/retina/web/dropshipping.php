<?php
/*
 * Author: Vika Aqordi <aqordivika@yahoo.co.id>
 * Created on: 19-08-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

use App\Actions\Dropshipping\ShopifyUser\DeleteShopifyUser;
use App\Actions\Dropshipping\ShopifyUser\StoreShopifyUser;
use App\Actions\UI\Retina\Dropshipping\Client\FetchCustomerClientFromShopify;
use App\Actions\UI\Retina\Dropshipping\Client\UI\CreateCustomerClient;
use App\Actions\UI\Retina\Dropshipping\Client\UI\IndexCustomerClients;
use App\Actions\UI\Retina\Dropshipping\Client\UI\ShowCustomerClient;
use App\Actions\UI\Retina\Dropshipping\Orders\IndexDropshippingRetinaOrders;
use App\Actions\UI\Retina\Dropshipping\Product\UI\IndexDropshippingRetinaPortfolio;
use App\Actions\UI\Retina\Dropshipping\Product\UI\IndexDropshippingRetinaProducts;
use App\Actions\UI\Retina\Dropshipping\ShowDropshipping;
use App\Actions\UI\Retina\Dropshipping\ShowProduct;
use Illuminate\Support\Facades\Route;

Route::prefix('platform')->as('platform.')->group(function () {
    Route::get('/', ShowDropshipping::class)->name('dashboard');

    Route::post('shopify-user', StoreShopifyUser::class)->name('shopify_user.store');
    Route::delete('shopify-user', DeleteShopifyUser::class)->name('shopify_user.delete');
});

Route::prefix('client')->as('client.')->group(function () {
    Route::get('/', IndexCustomerClients::class)->name('index');
    Route::get('create', CreateCustomerClient::class)->name('create');
    Route::get('fetch', FetchCustomerClientFromShopify::class)->name('fetch');
    Route::get('{customerClient}/show', ShowCustomerClient::class)->name('show');
});

Route::prefix('portfolios')->as('portfolios.')->group(function () {
    Route::get('my-portfolio', IndexDropshippingRetinaPortfolio::class)->name('index');
    Route::get('my-portfolio/{product}', ShowProduct::class)->name('show');
    Route::get('products', IndexDropshippingRetinaProducts::class)->name('products.index');
});

Route::prefix('orders')->as('orders.')->group(function () {
    Route::get('/', IndexDropshippingRetinaOrders::class)->name('index');
});

// Route::get('/users', IndexUsers::class)->name('web-users.index');
// Route::get('/users/{user}', ShowUser::class)->name('web-users.show');
// Route::get('/users/{user}/edit', EditUser::class)->name('web-users.edit');
