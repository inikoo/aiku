<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jan 2024 11:31:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Accounting\Invoice\UI\IndexInvoices;
use App\Actions\Accounting\Invoice\UI\ShowInvoice;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\Pallet\UI\CreatePallet;
use App\Actions\Fulfilment\Pallet\UI\EditPallet;
use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Fulfilment\Pallet\UI\ShowPallet;
use App\Actions\Fulfilment\PalletDelivery\UI\IndexPalletDeliveries;
use App\Actions\Fulfilment\PalletDelivery\UI\ShowPalletDelivery;
use App\Actions\Fulfilment\PalletReturn\UI\IndexPalletReturns;
use App\Actions\Fulfilment\PalletReturn\UI\ShowPalletReturn;
use App\Actions\Fulfilment\RecurringBill\UI\EditRecurringBill;
use App\Actions\Fulfilment\RecurringBill\UI\IndexRecurringBills;
use App\Actions\Fulfilment\RecurringBill\UI\ShowRecurringBill;

Route::get('', ShowFulfilment::class)->name('dashboard');


Route::get('/pallets', IndexPallets::class)->name('pallets.index');
Route::get('/returned-pallets', IndexPallets::class)->name('returned_pallets.index');

Route::get('/pallets/{pallet}', [ShowPallet::class, 'inFulfilment'])->name('pallets.show');
//Route::get('/pallets/create', CreatePallet::class)->name('pallets.create');
Route::get('/pallets/{pallet}/edit', [EditPallet::class, 'inFulfilment'])->name('pallets.edit');

Route::get('deliveries', IndexPalletDeliveries::class)->name('pallet-deliveries.index');
Route::get('deliveries/{palletDelivery}', ShowPalletDelivery::class)->name('pallet-deliveries.show');

Route::get('returns', IndexPalletReturns::class)->name('pallet-returns.index');
Route::get('returns/{palletReturn}', ShowPalletReturn::class)->name('pallet-returns.show');

Route::prefix('recurring_bills')->as('recurring_bills')->group(function () {
    Route::get('', IndexRecurringBills::class)->name('.index');
    Route::get('{recurringBill}', ShowRecurringBill::class)->name('.show');
    Route::get('{recurringBill}/edit', EditRecurringBill::class)->name('.edit');
});

Route::prefix('invoices')->as('invoices')->group(function () {
    Route::get('', [IndexInvoices::class, 'inFulfilment'])->name('.index');
    Route::get('{invoice}', [ShowInvoice::class, 'inFulfilment'])->name('.show');
});
