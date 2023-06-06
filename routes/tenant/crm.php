<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:43:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Sales\Customer\UI\IndexCustomers;
use App\Actions\Sales\Order\UI\IndexOrders;
use App\Actions\UI\CRM\CRMDashboard;

Route::get('/', [CRMDashboard::class,'inTenant'])->name('dashboard');
Route::get('/customers', [IndexCustomers::class, 'inTenant'])->name('customers.index');
Route::get('/orders', [IndexOrders::class, 'inTenant'])->name('orders.index');


Route::get('/{shop}', [CRMDashboard::class,'inShop'])->name('shop.dashboard');

Route::get('/{shop}/customers', [IndexCustomers::class, 'inShop'])->name('shop.customers.index');
Route::get('/{shop}/orders', [IndexOrders::class, 'inShop'])->name('shop.orders.index');
