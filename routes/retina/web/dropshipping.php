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
use App\Actions\UI\Retina\Dropshipping\ShowDropshipping;
use App\Actions\UI\Retina\Dropshipping\IndexProducts;
use App\Actions\UI\Retina\Dropshipping\ShowProduct;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowDropshipping::class)->name('dashboard');
Route::get('/products', IndexProducts::class)->name('products.index');
Route::get('/products/{product}', ShowProduct::class)->name('products.show');

Route::post('shopify-user', StoreShopifyUser::class)->name('shopify_user.store');
Route::delete('shopify-user', DeleteShopifyUser::class)->name('shopify_user.delete');

// Route::get('/users', IndexUsers::class)->name('web-users.index');
// Route::get('/users/{user}', ShowUser::class)->name('web-users.show');
// Route::get('/users/{user}/edit', EditUser::class)->name('web-users.edit');
