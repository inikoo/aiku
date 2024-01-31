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
use App\Actions\Fulfilment\PalletDelivery\UI\IndexPalletDeliveries;
use App\Actions\Fulfilment\PalletDelivery\UI\ShowPalletDelivery;
use App\Actions\OMS\Order\UI\ShowOrder;

//Route::get('', ShowFulfilmentCRMDashboard::class)->name('dashboard');

Route::get('', IndexFulfilmentCustomers::class)->name('index');
Route::get('create', CreateFulfilmentCustomer::class)->name('create');

Route::get('{fulfilmentCustomer}/edit', [EditCustomer::class, 'inShop'])->name('edit');

Route::prefix('{fulfilmentCustomer}')->as('show')->group(function () {
    Route::get('', ShowFulfilmentCustomer::class);
    Route::get('orders/{order}', [ShowOrder::class, 'inCustomerInShop'])->name('.orders.show');
    Route::get('web-users', [IndexWebUser::class, 'inCustomerInShop'])->name('.web-users.index');
    Route::get('web-users/{webUser}', [ShowWebUser::class, 'inCustomerInShop'])->name('.web-users.show');
    Route::get('web-users/{webUser}/edit', [EditWebUser::class, 'inCustomerInShop'])->name('.web-users.edit');

    Route::prefix('pallet-deliveries')->as('.pallet-deliveries.')->group(function () {
        Route::get('', [IndexPalletDeliveries::class, 'inFulfilmentCustomer'])->name('index');
        Route::get('{palletDelivery}', [ShowPalletDelivery::class, 'inFulfilmentCustomer'])->name('show');
    });
});
