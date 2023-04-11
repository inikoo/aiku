<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Apr 2023 21:10:01 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Accounting\Invoice\IndexInvoices;
use App\Actions\Accounting\Invoice\ShowInvoice;
use App\Actions\Accounting\Payment\UI\CreatePayment;
use App\Actions\Accounting\Payment\UI\EditPayment;
use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\Accounting\Payment\UI\ShowPayment;
use App\Actions\Accounting\PaymentAccount\UI\CreatePaymentAccount;
use App\Actions\Accounting\PaymentAccount\UI\IndexPaymentAccounts;
use App\Actions\Accounting\PaymentAccount\UI\ShowPaymentAccount;
use Illuminate\Support\Facades\Route;

if (empty($parent)) {
    $parent = 'tenant';
}



Route::get('/accounts', IndexPaymentAccounts::class)->name('payment-accounts.index');
Route::get('/accounts/create', CreatePaymentAccount::class)->name('payment-accounts.create');
Route::get('/accounts/{paymentAccount}', ShowPaymentAccount::class)->name('payment-accounts.show');
Route::get('/accounts/{paymentAccount}/payments', [IndexPayments::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.index');
Route::get('/accounts/{paymentAccount}/payments/create', [CreatePayment::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.create');
Route::get('/accounts/{paymentAccount}/payments/{payment}', [ShowPayment::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.show');
Route::get('/accounts/{paymentAccount}/payments/{payment}/edit', [EditPayment::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.edit');



Route::get('/payments', IndexPayments::class)->name('payments.index');
Route::get('/payments/create', CreatePayment::class)->name('payments.create');
Route::get('/payments/{payment}', ShowPayment::class)->name('payments.show');
Route::get('/payments/{payment}/edit', EditPayment::class)->name('payments.edit');

Route::get('/invoices', IndexInvoices::class)->name('invoices.index');
Route::get('/invoices/{invoice}', ShowInvoice::class)->name('invoices.show');
