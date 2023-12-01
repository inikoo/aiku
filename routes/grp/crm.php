<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:43:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Auth\WebUser\EditWebUser;
use App\Actions\Auth\WebUser\IndexWebUser;
use App\Actions\Auth\WebUser\ShowWebUser;
use App\Actions\CRM\Customer\UI\CreateCustomer;
use App\Actions\CRM\Customer\UI\EditCustomer;
use App\Actions\CRM\Customer\UI\IndexCustomers;
use App\Actions\CRM\Customer\UI\RemoveCustomer;
use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\CRM\Prospect\IndexProspects;
use App\Actions\OMS\Order\UI\ShowOrder;
use App\Actions\UI\CRM\CRMDashboard;

Route::get('/', [CRMDashboard::class,'inTenant'])->name('dashboard');
Route::get('/customers', [IndexCustomers::class, 'inTenant'])->name('customers.index');
Route::get('/customers/{customer}', [ShowCustomer::class, 'inTenant'])->name('customers.show');
Route::get('/customers/{customer}/edit', [EditCustomer::class, 'inTenant'])->name('customers.edit');
Route::get('/customers/{customer}/delete', RemoveCustomer::class)->name('customers.remove');
Route::get('/customers/{customer}/orders/{order}', [ShowOrder::class,'inCustomerInTenant'])->name('customers.show.orders.show');
Route::get('/customers/{customer}/web-users', [IndexWebUser::class, 'inCustomerInTenant'])->name('customers.show.web-users.index');
Route::get('/customers/{customer}/web-users/{webUser}', [ShowWebUser::class, 'inCustomerInTenant'])->name('customers.show.web-users.show');
Route::get('/customers/{customer}/web-users/{webUser}/edit', [EditWebUser::class, 'inCustomerInTenant'])->name('customers.show.web-users.edit');


Route::get('/prospects', [IndexProspects::class, 'inTenant'])->name('prospects.index');


Route::get('/shop/{shop}', [CRMDashboard::class,'inShop'])->name('shops.show.dashboard');

Route::get('/shop/{shop}/customers/create', CreateCustomer::class)->name('shops.show.customers.create');
Route::get('/shop/{shop}/customers', [IndexCustomers::class, 'inShop'])->name('shops.show.customers.index');
Route::get('/shop/{shop}/customers/{customer}', [ShowCustomer::class, 'inShop'])->name('shops.show.customers.show');
Route::get('/shop/{shop}/customers/{customer}/edit', [EditCustomer::class, 'inShop'])->name('shops.show.customers.edit');
Route::get('/shop/{shop}/customers/{customer}/orders/{order}', [ShowOrder::class,'inCustomerInShop'])->name('shops.show.customers.show.orders.show');
Route::get('/shop/{shop}/customers/{customer}/web-users', [IndexWebUser::class, 'inCustomerInShop'])->name('shops.show.customers.show.web-users.index');
Route::get('/shop/{shop}/customers/{customer}/web-users/{webUser}', [ShowWebUser::class, 'inCustomerInShop'])->name('shops.show.customers.show.web-users.show');
Route::get('/shop/{shop}/customers/{customer}/web-users/{webUser}/edit', [EditWebUser::class, 'inCustomerInShop'])->name('shops.show.customers.show.web-users.edit');


Route::get('/shop/{shop}/prospects', [IndexProspects::class, 'inShop'])->name('shops.show.prospects.index');
