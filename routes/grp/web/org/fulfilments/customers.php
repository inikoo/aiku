<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Jan 2024 17:46:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\CRM\Customer\UI\EditCustomer;
use App\Actions\CRM\WebUser\EditWebUser;
use App\Actions\CRM\WebUser\IndexWebUser;
use App\Actions\CRM\WebUser\ShowWebUser;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\UI\CreateFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\UI\IndexFulfilmentCustomers;
use App\Actions\Fulfilment\PalletDelivery\UI\CreatePalletDelivery;
use App\Actions\OMS\Order\UI\ShowOrder;

//Route::get('', ShowFulfilmentCRMDashboard::class)->name('dashboard');

Route::get('', IndexFulfilmentCustomers::class)->name('index');
Route::get('create', CreateFulfilmentCustomer::class)->name('create');
Route::get('{customer}', ShowFulfilmentCustomer::class)->name('show');
Route::get('{customer}/edit', [EditCustomer::class, 'inShop'])->name('edit');
Route::get('{customer}/orders/{order}', [ShowOrder::class, 'inCustomerInShop'])->name('show.orders.show');
Route::get('{customer}/web-users', [IndexWebUser::class, 'inCustomerInShop'])->name('show.web-users.index');
Route::get('{customer}/web-users/{webUser}', [ShowWebUser::class, 'inCustomerInShop'])->name('show.web-users.show');
Route::get('{customer}/web-users/{webUser}/edit', [EditWebUser::class, 'inCustomerInShop'])->name('show.web-users.edit');

Route::prefix('deliveries')->as('deliveries.')->group(function () {
    Route::get('create', CreatePalletDelivery::class)->name('create');
});

/*

Route::prefix('prospects')->as('prospects.')->group(function () {
    Route::get('/', [IndexProspects::class, 'inShop'])->name('index');
    Route::get('/', ['icon' => 'fa-envelope', 'label' => 'prospects'])->uses([IndexProspects::class, 'inShop'])->name('index');
    Route::get('/create', ['icon' => 'fa-envelope', 'label' => 'create prospect'])->uses([CreateProspect::class, 'inShop'])->name('create');
});
*/
