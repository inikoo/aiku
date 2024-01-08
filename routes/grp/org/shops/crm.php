<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Jan 2024 17:46:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\CRM\Customer\UI\CreateCustomer;
use App\Actions\CRM\Customer\UI\EditCustomer;
use App\Actions\CRM\Customer\UI\IndexCustomers;
use App\Actions\CRM\Customer\UI\RemoveCustomer;
use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\CRM\Prospect\IndexProspects;
use App\Actions\CRM\WebUser\EditWebUser;
use App\Actions\CRM\WebUser\IndexWebUser;
use App\Actions\CRM\WebUser\ShowWebUser;
use App\Actions\OMS\Order\UI\ShowOrder;
use App\Actions\UI\CRM\CRMDashboard;

/*
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
*/

Route::get('', [CRMDashboard::class,'inShop'])->name('dashboard');

Route::get('/customers/create', CreateCustomer::class)->name('customers.create');
Route::get('/customers', [IndexCustomers::class, 'inShop'])->name('customers.index');
Route::get('/customers/{customer}', [ShowCustomer::class, 'inShop'])->name('customers.show');
Route::get('/customers/{customer}/edit', [EditCustomer::class, 'inShop'])->name('customers.edit');
Route::get('/customers/{customer}/orders/{order}', [ShowOrder::class,'inCustomerInShop'])->name('customers.show.orders.show');
Route::get('/customers/{customer}/web-users', [IndexWebUser::class, 'inCustomerInShop'])->name('customers.show.web-users.index');
Route::get('/customers/{customer}/web-users/{webUser}', [ShowWebUser::class, 'inCustomerInShop'])->name('customers.show.web-users.show');
Route::get('/customers/{customer}/web-users/{webUser}/edit', [EditWebUser::class, 'inCustomerInShop'])->name('customers.show.web-users.edit');


Route::get('/prospects', [IndexProspects::class, 'inShop'])->name('prospects.index');
