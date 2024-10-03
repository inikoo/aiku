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
use App\Actions\UI\Retina\Dropshipping\Client\CreateCustomerClient;
use App\Actions\UI\Retina\Dropshipping\Client\IndexCustomerClients;
use App\Actions\UI\Retina\Dropshipping\ShowDropshipping;
use App\Actions\UI\Retina\Dropshipping\IndexDropshippingRetinaProducts;
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
});

Route::prefix('portfolios')->as('portfolios.')->group(function () {
    Route::get('/', IndexDropshippingRetinaProducts::class)->name('index');
    Route::get('{portfolio}', ShowProduct::class)->name('show');
});

// Route::get('/users', IndexUsers::class)->name('web-users.index');
// Route::get('/users/{user}', ShowUser::class)->name('web-users.show');
// Route::get('/users/{user}/edit', EditUser::class)->name('web-users.edit');
