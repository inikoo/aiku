<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:42:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Accounting\Payment\UI\CreatePayment;
use App\Actions\Accounting\Payment\UI\EditPayment;
use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\Accounting\Payment\UI\ShowPayment;
use App\Actions\Accounting\PaymentAccount\UI\CreatePaymentAccount;
use App\Actions\Accounting\PaymentAccount\UI\IndexPaymentAccounts;
use App\Actions\Accounting\PaymentAccount\UI\ShowPaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\IndexPaymentServiceProviders;
use App\Actions\Accounting\PaymentServiceProvider\ShowPaymentServiceProvider;
use App\Actions\Sales\Invoice\IndexInvoices;
use App\Actions\UI\Accounting\AccountingDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', AccountingDashboard::class)->name('dashboard');

Route::get('/providers', IndexPaymentServiceProviders::class)->name('payment-service-providers.index');
Route::get('/providers/{paymentServiceProvider}', ShowPaymentServiceProvider::class)->name('payment-service-providers.show');
Route::get('/providers/{paymentServiceProvider}/accounts', [IndexPaymentAccounts::class, 'inPaymentServiceProvider'])->name('payment-service-providers.show.payment-accounts.index');
Route::get('/providers/{paymentServiceProvider}/accounts/{paymentAccount}', [ShowPaymentAccount::class, 'inPaymentServiceProvider'])->name('payment-service-providers.show.payment-accounts.show');
Route::get('/providers/{paymentServiceProvider}/accounts/{paymentAccount}/payments', [IndexPayments::class, 'inPaymentAccountInPaymentServiceProvider'])->name('payment-service-providers.show.payment-accounts.show.payments.index');
Route::get('/providers/{paymentServiceProvider}/accounts/{paymentAccount}/payments/create', [IndexPayments::class, 'inPaymentAccountInPaymentServiceProvider'])->name('payment-service-providers.show.payment-accounts.show.payments.create');

Route::get('/providers/{paymentServiceProvider}/accounts/{paymentAccount}/payments/{payment}', [ShowPayment::class, 'inPaymentAccountInPaymentServiceProvider'])->name('payment-service-providers.show.payment-accounts.show.payments.show');

Route::get('/providers/{paymentServiceProvider}/payments', [IndexPayments::class, 'inPaymentServiceProvider'])->name('payment-service-providers.show.payments.index');
Route::get('/providers/{paymentServiceProvider}/payments/create', [IndexPayments::class, 'inPaymentServiceProvider'])->name('payment-service-providers.show.payments.create');

Route::get('/providers/{paymentServiceProvider}/payments/{payment}', [ShowPayment::class, 'inPaymentServiceProvider'])->name('payment-service-providers.show.payments.show');


Route::get('/accounts', IndexPaymentAccounts::class)->name('payment-accounts.index');
Route::get('/accounts/create', CreatePaymentAccount::class)->name('payment-accounts.create');
Route::get('/accounts/{paymentAccount}', ShowPaymentAccount::class)->name('payment-accounts.show');
Route::get('/accounts/{paymentAccount}/payments', [IndexPayments::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.index');
Route::get('/accounts/{paymentAccount}/payments/create', [CreatePayment::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.create');
Route::get('/accounts/{paymentAccount}/payments/{payment}', [ShowPayment::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.show');




Route::get('/payments', IndexPayments::class)->name('payments.index');
Route::get('/payments/create', CreatePayment::class)->name('payments.create');
Route::get('/payments/{payment}', ShowPayment::class)->name('payments.show');
Route::get('/payments/{payment}/edit', EditPayment::class)->name('payments.edit');

Route::get('/invoices', IndexInvoices::class)->name('show.invoices.index');
