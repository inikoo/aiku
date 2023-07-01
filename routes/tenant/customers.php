<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 16:07:09 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\CRM\Customer\UI\CreateCustomer;
use App\Actions\CRM\Customer\UI\EditCustomer;
use App\Actions\CRM\Customer\UI\IndexCustomers;
use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\OMS\Order\UI\ShowOrder;
use App\Actions\Web\WebUser\CreateWebUser;
use App\Actions\Web\WebUser\IndexWebUser;
use App\Actions\Web\WebUser\ShowWebUser;
use Illuminate\Support\Facades\Route;

if (empty($parent)) {
    $parent = 'tenant';
}



Route::get('/create', CreateCustomer::class)->name('create');
Route::get('/{customer}/web-users/create', [CreateWebUser::class, 'inCustomer'])->name('show.web-users.create');


Route::get('/', [IndexCustomers::class , $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('index');
Route::get('/{customer}', [ShowCustomer::class , $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('show');
Route::get('/{customer}/edit', [EditCustomer::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('edit');
Route::get('/{customer}/orders/{order}', [ShowOrder::class,$parent == 'tenant' ? 'inCustomerInTenant' : 'inCustomerInShop'])->name('show.orders.show');
Route::get('/{customer}/web-users', [IndexWebUser::class, $parent == 'tenant' ? 'inCustomerInTenant' : 'inCustomerInShop'])->name('show.web-users.index');
Route::get('/{customer}/web-users/{webUser}', [ShowWebUser::class, $parent == 'tenant' ? 'inCustomerInTenant' : 'inCustomerInShop'])->name('show.web-users.show');
