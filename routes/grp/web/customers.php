<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Jan 2024 03:24:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\CRM\Customer\UI\CreateCustomer;
use App\Actions\CRM\Customer\UI\EditCustomer;
use App\Actions\CRM\Customer\UI\IndexCustomers;
use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\CRM\WebUser\CreateWebUser;
use App\Actions\CRM\WebUser\IndexWebUser;
use App\Actions\CRM\WebUser\ShowWebUser;
use App\Actions\OMS\Order\UI\ShowOrder;
use Illuminate\Support\Facades\Route;

if (empty($parent)) {
    $parent = 'tenant';
}



Route::get('/create', CreateCustomer::class)->name('create');
Route::get('/{customer}/web-users/create', [CreateWebUser::class, 'inCustomer'])->name('show.web-users.create');


Route::get('/', [IndexCustomers::class , $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('index');
Route::get('/{customer}', [ShowCustomer::class , $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('show');
Route::get('/{customer}/edit', [EditCustomer::class, $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('edit');
Route::get('/{customer}/orders/{order}', [ShowOrder::class,$parent == 'tenant' ? 'inCustomerInTenant' : 'inCustomerInShop'])->name('show.orders.show');
Route::get('/{customer}/web-users', [IndexWebUser::class, $parent == 'tenant' ? 'inCustomerInTenant' : 'inCustomerInShop'])->name('show.web-users.index');
Route::get('/{customer}/web-users/{webUser}', [ShowWebUser::class, $parent == 'tenant' ? 'inCustomerInTenant' : 'inCustomerInShop'])->name('show.web-users.show');
