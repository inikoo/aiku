<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Jan 2024 17:46:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\CRM\Customer\UI\CreateCustomer;
use App\Actions\CRM\Customer\UI\EditCustomer;
use App\Actions\CRM\Customer\UI\IndexCustomers;
use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\CRM\WebUser\EditWebUser;
use App\Actions\CRM\WebUser\IndexWebUsers;
use App\Actions\CRM\WebUser\ShowWebUser;
use App\Actions\OMS\Order\UI\ShowOrder;

Route::get('', IndexCustomers::class)->name('index');
Route::get('create', CreateCustomer::class)->name('create');
Route::get('{customer}', ShowCustomer::class)->name('show');
Route::get('{customer}/edit', EditCustomer::class)->name('edit');
Route::get('{customer}/orders/{order}', [ShowOrder::class, 'inCustomerInShop'])->name('show.orders.show');
Route::get('{customer}/web-users', [IndexWebUsers::class, 'inCustomerInShop'])->name('show.web-users.index');
Route::get('{customer}/web-users/{webUser}', [ShowWebUser::class, 'inCustomerInShop'])->name('show.web-users.show');
Route::get('{customer}/web-users/{webUser}/edit', [EditWebUser::class, 'inCustomerInShop'])->name('show.web-users.edit');
