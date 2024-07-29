<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Jan 2024 17:46:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Accounting\Invoice\UI\IndexInvoices;
use App\Actions\Accounting\Invoice\UI\ShowInvoice;
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
use App\Actions\Fulfilment\Pallet\UI\IndexPalletsInCustomer;
use App\Actions\Fulfilment\Pallet\UI\ShowPallet;
use App\Actions\Fulfilment\PalletDelivery\UI\IndexPalletDeliveries;
use App\Actions\Fulfilment\PalletDelivery\UI\ShowPalletDelivery;
use App\Actions\Fulfilment\PalletReturn\ExportPalletReturnStoredItem;
use App\Actions\Fulfilment\PalletReturn\UI\IndexPalletReturns;
use App\Actions\Fulfilment\PalletReturn\UI\ShowPalletReturn;
use App\Actions\Fulfilment\RecurringBill\UI\EditRecurringBill;
use App\Actions\Fulfilment\RecurringBill\UI\IndexRecurringBills;
use App\Actions\Fulfilment\RecurringBill\UI\ShowRecurringBill;
use App\Actions\Fulfilment\RentalAgreement\UI\CreateRentalAgreement;
use App\Actions\Fulfilment\RentalAgreement\UI\EditRentalAgreement;
use App\Actions\Fulfilment\StoredItem\UI\EditStoredItem;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItems;
use App\Actions\Fulfilment\StoredItem\UI\ShowStoredItem;
use App\Actions\Fulfilment\StoredItemAudit\UI\CreateStoredItemAudit;
use App\Actions\Fulfilment\StoredItemAudit\UI\IndexStoredItemAudits;
use App\Actions\Fulfilment\StoredItemAudit\UI\ShowStoredItemAudit;
use App\Actions\Helpers\Uploads\HistoryUploads;

//Route::get('', ShowFulfilmentCRMDashboard::class)->name('dashboard');

Route::get('', IndexFulfilmentCustomers::class)->name('index');
Route::get('create', CreateFulfilmentCustomer::class)->name('create');

Route::get('{fulfilmentCustomer}/edit', [EditCustomer::class, 'inShop'])->name('edit');

Route::prefix('{fulfilmentCustomer}')->as('show')->group(function () {
    Route::get('', ShowFulfilmentCustomer::class);
    Route::get('/edit', EditFulfilmentCustomer::class)->name('.edit');

    Route::get('/rental-agreement', CreateRentalAgreement::class)->name('.rental-agreement.create');
    Route::get('/rental-agreement/edit', EditRentalAgreement::class)->name('.rental-agreement.edit');

    Route::get('webhook', FetchNewWebhookFulfilmentCustomer::class)->name('.webhook.fetch');


    Route::prefix('web-users')->as('.web-users.')->group(function () {
        Route::get('', [IndexWebUsers::class, 'inFulfilmentCustomer'])->name('index');
        Route::get('create', [CreateWebUser::class,'inFulfilmentCustomer'])->name('create');
        Route::prefix('{webUser}')->group(function () {
            Route::get('', [ShowWebUser::class, 'inFulfilmentCustomer'])->name('show');
            Route::get('edit', [EditWebUser::class, 'inFulfilmentCustomer'])->name('edit');
        });
    });




    Route::get('stored-items', [IndexStoredItems::class, 'inFulfilmentCustomer'])->name('.stored-items.index');
    Route::get('stored-items/{storedItem}', [ShowStoredItem::class, 'inFulfilmentCustomer'])->name('.stored-items.show');
    Route::get('stored-items/{storedItem}/edit', [EditStoredItem::class, 'inFulfilmentCustomer'])->name('.stored-items.edit');

    Route::prefix('pallets')->as('.pallets.')->group(function () {
        Route::get('', IndexPalletsInCustomer::class)->name('index');
        Route::get('{pallet}', [ShowPallet::class, 'inFulfilmentCustomer'])->name('show');
    });

    Route::prefix('pallet-deliveries')->as('.pallet_deliveries.')->group(function () {
        Route::get('', [IndexPalletDeliveries::class, 'inFulfilmentCustomer'])->name('index');
        Route::get('{palletDelivery}', [ShowPalletDelivery::class, 'inFulfilmentCustomer'])->name('show');
        Route::get('{palletDelivery}/pallets/{pallet}', [ShowPallet::class, 'inFulfilmentCustomer'])->name('pallets.show');

        Route::get('{palletDelivery}/pallets-histories', [HistoryUploads::class, 'inPalletDelivery'])->name('pallets.uploads.history');
        Route::get('{palletDelivery}/pallets-templates', DownloadPalletsTemplate::class)->name('pallets.uploads.templates');
    });

    Route::prefix('pallet-returns')->as('.pallet_returns.')->group(function () {
        Route::get('', [IndexPalletReturns::class, 'inFulfilmentCustomer'])->name('index');
        Route::get('{palletReturn}', [ShowPalletReturn::class, 'inFulfilmentCustomer'])->name('show');
        Route::get('{palletReturn}/pallets/{pallet}', [ShowPallet::class, 'inFulfilmentCustomer'])->name('pallets.show');

        Route::get('pallets-stored-items/export', ExportPalletReturnStoredItem::class)->name('pallets.stored-items.export');
        Route::get('{palletReturn}/pallets-histories', [HistoryUploads::class, 'inPalletReturn'])->name('pallets.uploads.history');
        Route::get('{palletReturn}/pallets-templates', [DownloadPalletsTemplate::class, 'inReturn'])->name('pallets.uploads.templates');
    });

    Route::prefix('recurring-bills')->as('.recurring_bills.')->group(function () {
        Route::get('', [IndexRecurringBills::class, 'inFulfilmentCustomer'])->name('index');
        Route::get('{recurringBill}', [ShowRecurringBill::class, 'inFulfilmentCustomer'])->name('show');
        Route::get('{recurringBill}/edit', [EditRecurringBill::class, 'inFulfilmentCustomer'])->name('edit');
    });

    Route::prefix('invoices')->as('.invoices.')->group(function () {
        Route::get('', [IndexInvoices::class, 'inFulfilmentCustomer'])->name('index');
        Route::get('{invoice}', [ShowInvoice::class, 'inFulfilmentCustomer'])->name('show');
    });

    Route::get('/stored-item-audits', [IndexStoredItemAudits::class, 'inFulfilmentCustomer'])->name('.stored-item-audits.index');
    Route::get('/stored-item-audits/create', [CreateStoredItemAudit::class, 'inFulfilmentCustomer'])->name('.stored-item-audits.create');
    Route::get('/stored-item-audits/{storedItemAudit}', [ShowStoredItemAudit::class, 'inFulfilmentCustomer'])->name('.stored-item-audits.show');
});
