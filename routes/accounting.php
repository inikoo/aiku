<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 01 Mar 2023 14:51:52 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Accounting\Payment\IndexPayments;
use App\Actions\Accounting\Payment\ShowPayment;
use App\Actions\Accounting\PaymentAccount\IndexPaymentAccounts;
use App\Actions\Accounting\PaymentAccount\ShowPaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\IndexPaymentServiceProviders;
use App\Actions\Accounting\PaymentServiceProvider\ShowPaymentServiceProvider;
use App\Actions\Accounting\ShowAccountingDashboard;
use App\Actions\Sales\Invoice\IndexInvoices;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowAccountingDashboard::class)->name('dashboard');

Route::get('/providers', IndexPaymentServiceProviders::class)->name('payment-service-providers.index');
Route::get('/providers/{paymentServiceProvider}', ShowPaymentServiceProvider::class)->name('payment-service-providers.show');
Route::get('/providers/{paymentServiceProvider}/accounts', [IndexPaymentAccounts::class, 'inPaymentServiceProvider'])->name('payment-service-providers.show.payment-accounts.index');
Route::get('/providers/{paymentServiceProvider}/accounts/{paymentAccount}', [ShowPaymentAccount::class, 'inPaymentServiceProvider'])->name('payment-service-providers.show.payment-accounts.show');
Route::get('/providers/{paymentServiceProvider}/accounts/{paymentAccount}/payments', [IndexPayments::class, 'inPaymentAccountInPaymentServiceProvider'])->name('payment-service-providers.show.payment-accounts.show.payments.index');
Route::get('/providers/{paymentServiceProvider}/accounts/{paymentAccount}/payments/{payment}', [ShowPayment::class, 'inPaymentAccountInPaymentServiceProvider'])->name('payment-service-providers.show.payment-accounts.show.payments.show');

Route::get('/providers/{paymentServiceProvider}/payments', [IndexPayments::class, 'inPaymentServiceProvider'])->name('payment-service-providers.show.payments.index');
Route::get('/providers/{paymentServiceProvider}/payments/{payment}', [ShowPayment::class, 'inPaymentServiceProvider'])->name('payment-service-providers.show.payments.show');


Route::get('/accounts', IndexPaymentAccounts::class)->name('payment-accounts.index');
Route::get('/accounts/{paymentAccount}', ShowPaymentAccount::class)->name('payment-accounts.show');
Route::get('/accounts/{paymentAccount}/payments', [IndexPayments::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.index');
Route::get('/accounts/{paymentAccount}/payments/{payment}', [ShowPayment::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.show');




Route::get('/payments', IndexPayments::class)->name('payments.index');
Route::get('/payments/{payment}', ShowPayment::class)->name('payments.show');

Route::get('/invoices', IndexInvoices::class)->name('show.invoices.index');
