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
use App\Actions\Fulfilment\FulfilmentCustomer\FetchNewWebhookFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\UI\CreateFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\UI\EditFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\UI\IndexFulfilmentCustomers;
use App\Actions\Fulfilment\Pallet\DownloadPalletsTemplate;
use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Fulfilment\Pallet\UI\ShowPallet;
use App\Actions\Fulfilment\PalletDelivery\UI\IndexPalletDeliveries;
use App\Actions\Fulfilment\PalletDelivery\UI\ShowPalletDelivery;
use App\Actions\Fulfilment\PalletReturn\IndexStoredPallets;
use App\Actions\Fulfilment\PalletReturn\UI\IndexPalletReturns;
use App\Actions\Fulfilment\PalletReturn\UI\ShowPalletReturn;
use App\Actions\Fulfilment\Proforma\UI\IndexProforma;
use App\Actions\Fulfilment\RentalAgreement\UI\CreateRentalAgreement;
use App\Actions\Fulfilment\RentalAgreement\UI\EditRentalAgreement;
use App\Actions\Fulfilment\StoredItem\UI\IndexBookedInStoredItems;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItems;
use App\Actions\Fulfilment\StoredItem\UI\ShowStoredItem;
use App\Actions\Fulfilment\StoredItemReturn\UI\ShowStoredItemReturn;
use App\Actions\Helpers\Uploads\HistoryUploads;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\OMS\Order\UI\ShowOrder;

//Route::get('', ShowFulfilmentCRMDashboard::class)->name('dashboard');

Route::get('', IndexFulfilmentCustomers::class)->name('index');
Route::get('create', CreateFulfilmentCustomer::class)->name('create');

Route::get('{fulfilmentCustomer}/edit', [EditCustomer::class, 'inShop'])->name('edit');

Route::prefix('{fulfilmentCustomer}')->as('show')->group(function () {
    Route::get('', ShowFulfilmentCustomer::class);
    Route::get('/edit', EditFulfilmentCustomer::class)->name('.edit');
    Route::get('orders/{order}', [ShowOrder::class, 'inCustomerInShop'])->name('.orders.show');

    Route::get('/rental-agreement', CreateRentalAgreement::class)->name('.rental-agreement.create');
    Route::get('/rental-agreement/edit', EditRentalAgreement::class)->name('.rental-agreement.edit');

    Route::prefix('web-users')->as('.web-users.')->group(function () {
        Route::get('', [IndexWebUsers::class, 'inFulfilmentCustomer'])->name('index');
        Route::get('create', [CreateWebUser::class,'inFulfilmentCustomer'])->name('create');
        Route::prefix('{webUser}')->group(function () {
            Route::get('', [ShowWebUser::class, 'inFulfilmentCustomer'])->name('show');
            Route::get('edit', [EditWebUser::class, 'inFulfilmentCustomer'])->name('edit');
        });
    });

    Route::get('webhook', FetchNewWebhookFulfilmentCustomer::class)->name('.webhook.fetch');

    Route::get('pallets/stored', [IndexStoredPallets::class, 'inFulfilmentCustomer'])->name('.stored-pallets.index');
    Route::get('stored-items', [IndexStoredItems::class, 'inFulfilmentCustomer'])->name('.stored-items.index');
    Route::get('stored-items/{storedItem}', [ShowStoredItem::class, 'inFulfilmentCustomer'])->name('.stored-items.show');

    Route::prefix('pallets')->as('.pallets.')->group(function () {
        Route::get('', [IndexPallets::class, 'inFulfilmentCustomer'])->name('index');
        Route::get('{pallet}', [ShowPallet::class, 'inFulfilmentCustomer'])->name('show');
        Route::get('{pallet:id}/locations', [IndexLocations::class, 'fromPallet'])->name('locations.index');
    });

    Route::prefix('pallet-deliveries')->as('.pallet-deliveries.')->group(function () {
        Route::get('', [IndexPalletDeliveries::class, 'inFulfilmentCustomer'])->name('index');
        Route::get('{palletDelivery}', [ShowPalletDelivery::class, 'inFulfilmentCustomer'])->name('show');
        Route::get('{palletDelivery}/pallets/{pallet}', [ShowPallet::class, 'inFulfilmentCustomer'])->name('pallets.show');

        Route::get('{palletDelivery}/pallets-histories', [HistoryUploads::class, 'inPallet'])->name('pallets.uploads.history');
        Route::get('{palletDelivery}/pallets-templates', DownloadPalletsTemplate::class)->name('pallets.uploads.templates');
    });

    Route::prefix('pallet-returns')->as('.pallet-returns.')->group(function () {
        Route::get('', [IndexPalletReturns::class, 'inFulfilmentCustomer'])->name('index');
        Route::get('{palletReturn}', [ShowPalletReturn::class, 'inFulfilmentCustomer'])->name('show');
        Route::get('{palletReturn}/pallets/{pallet}', [ShowPallet::class, 'inFulfilmentCustomer'])->name('pallets.show');
    });

    Route::prefix('stored-item-returns')->as('.stored-item-returns.')->group(function () {
        Route::get('{storedItemReturn}', [ShowStoredItemReturn::class, 'inFulfilmentCustomer'])->name('show');
        Route::get('stored-items/booked-in', [IndexBookedInStoredItems::class, 'inFulfilmentCustomer'])->name('stored-items.booked-in.index');
    });

    Route::prefix('proformas')->as('.proformas.')->group(function () {
        Route::get('', [IndexProforma::class, 'inFulfilmentCustomer'])->name('index');
        Route::get('{palletReturn}', [ShowPalletReturn::class, 'inFulfilmentCustomer'])->name('show');
    });
});
