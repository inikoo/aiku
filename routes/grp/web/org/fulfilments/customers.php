<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Jan 2024 17:46:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\CRM\Customer\UI\EditCustomer;
use App\Actions\CRM\WebUser\CreateWebUser;
use App\Actions\CRM\WebUser\EditWebUser;
use App\Actions\CRM\WebUser\IndexWebUsers;
use App\Actions\CRM\WebUser\ShowWebUser;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\UI\CreateFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\UI\IndexFulfilmentCustomers;
use App\Actions\Fulfilment\Pallet\DownloadPalletsTemplate;
use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Fulfilment\Pallet\UI\ShowPallet;
use App\Actions\Fulfilment\PalletDelivery\UI\IndexPalletDeliveries;
use App\Actions\Fulfilment\PalletDelivery\UI\ShowPalletDelivery;
use App\Actions\Helpers\Uploads\HistoryUploads;
use App\Actions\OMS\Order\UI\ShowOrder;

//Route::get('', ShowFulfilmentCRMDashboard::class)->name('dashboard');

Route::get('', IndexFulfilmentCustomers::class)->name('index');
Route::get('create', CreateFulfilmentCustomer::class)->name('create');

Route::get('{fulfilmentCustomer}/edit', [EditCustomer::class, 'inShop'])->name('edit');

Route::prefix('{fulfilmentCustomer}')->as('show')->group(function () {
    Route::get('', ShowFulfilmentCustomer::class);
    Route::get('orders/{order}', [ShowOrder::class, 'inCustomerInShop'])->name('.orders.show');

    Route::prefix('web-users')->as('.web-users.')->group(function () {
        Route::get('', [IndexWebUsers::class, 'inCustomerInShop'])->name('index');
        Route::get('create', [CreateWebUser::class,'inFulfilmentCustomer'])->name('create');
        Route::prefix('{webUser}')->group(function () {
            Route::get('', [ShowWebUser::class, 'inFulfilmentCustomer'])->name('show');
            Route::get('edit', [EditWebUser::class, 'inCustomerInShop'])->name('edit');
        });
    });

    Route::prefix('pallets')->as('.pallets.')->group(function () {
        Route::get('', [IndexPallets::class, 'inFulfilmentCustomer'])->name('index');
        Route::get('{pallet}', [ShowPallet::class, 'inFulfilmentCustomer'])->name('show');
    });

    Route::prefix('pallet-deliveries')->as('.pallet-deliveries.')->group(function () {
        Route::get('', [IndexPalletDeliveries::class, 'inFulfilmentCustomer'])->name('index');
        Route::get('{palletDelivery}', [ShowPalletDelivery::class, 'inFulfilmentCustomer'])->name('show');
        Route::get('{palletDelivery}/pallets/{pallet}', [ShowPallet::class, 'inFulfilmentCustomer'])->name('pallets.show');

        Route::get('{palletDelivery}/pallets-histories', [HistoryUploads::class, 'inPallet'])->name('pallets.uploads.history');
        Route::get('{palletDelivery}/pallets-templates', DownloadPalletsTemplate::class)->name('pallets.uploads.templates');
    });
});
