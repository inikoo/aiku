<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 13 Sept 2024 13:14:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Accounting\Invoice\PayInvoice;
use App\Actions\Accounting\Invoice\UpdateInvoice;
use App\Actions\Accounting\InvoiceTransaction\DeleteInvoiceTransaction;
use Illuminate\Support\Facades\Route;

Route::name('invoice.')->prefix('invoice/{invoice:id}')->group(function () {
    Route::patch('update', UpdateInvoice::class)->name('update');
    Route::post('customer/{customer:id}/payment/{paymentAccount:id}', PayInvoice::class)->name('payment.store')->withoutScopedBindings();
});

Route::name('invoice_transaction.')->prefix('invoice_transaction/{invoiceTransaction:id}')->group(function () {
    Route::delete('delete', DeleteInvoiceTransaction::class)->name('delete');
});
