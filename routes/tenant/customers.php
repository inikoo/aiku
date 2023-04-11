<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 16:07:09 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Sales\Customer\UI\CreateCustomer;
use App\Actions\Sales\Customer\UI\EditCustomer;
use App\Actions\Sales\Customer\UI\IndexCustomers;
use App\Actions\Sales\Customer\UI\ShowCustomer;
use App\Actions\Sales\Order\ShowOrder;
use App\Actions\Web\WebUser\CreateWebUser;
use App\Actions\Web\WebUser\IndexWebUser;
use App\Actions\Web\WebUser\ShowWebUser;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexCustomers::class)->name('index');
Route::get('/{customer}', ShowCustomer::class)->name('show');
Route::get('/{customer}/edit', [ShowCustomer::class, 'inShop'])->name('edit');
Route::get('/{customer}/orders/{order}', [ShowOrder::class,'inCustomer'])->name('show.orders.show');




Route::get('/{customer}/web-users', [IndexWebUser::class, 'inCustomer'])->name('show.web-users.index');
Route::get('/{customer}/web-users/{webUser}', [ShowWebUser::class, 'inCustomer'])->name('show.web-users.show');
Route::get('/{customer}/web-users/create', [CreateWebUser::class, 'inCustomer'])->name('show.web-users.create');



Route::get('/customers', [IndexCustomers::class, 'inShop'])->name('show.customers.index');
Route::get('/customers/create', CreateCustomer::class)->name('show.customers.create');
Route::get('/customers/{customer}', [ShowCustomer::class, 'inShop'])->name('show.customers.show');
Route::get('/customers/{customer}/edit', [EditCustomer::class, 'inShop'])->name('show.customers.edit');
Route::get('/customers/{customer}/orders/{order}', [ShowOrder::class, 'inCustomerInShop'])->name('show.customers.show.orders.show');


Route::get('/customers/{customer}/web-users', [IndexWebUser::class, 'inShopInCustomer'])->name('show.customers.show.web-users.index');
Route::get('/customers/{customer}/web-users/{webUser}', [ShowWebUser::class, 'inShopInCustomer'])->name('show.customers.show.web-users.show');


Route::get('/customers/{customer}/web-users/create', [CreateWebUser::class, 'inShopInCustomer'])->name('show.customers.show.web-users.create');
