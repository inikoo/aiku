<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 16 Mar 2024 20:36:21 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Accounting\Invoice\PdfInvoice;
use App\Actions\Accounting\Invoice\ExportInvoices;
use App\Actions\Accounting\Invoice\UI\IndexInvoices;
use App\Actions\Accounting\Invoice\UI\ShowInvoice;
use App\Actions\Accounting\OrgPaymentServiceProvider\UI\SelectOrgPaymentServiceProviders;
use App\Actions\Accounting\OrgPaymentServiceProvider\UI\ShowOrgPaymentServiceProvider;
use App\Actions\Accounting\Payment\ExportPayments;
use App\Actions\Accounting\Payment\UI\CreatePayment;
use App\Actions\Accounting\Payment\UI\EditPayment;
use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\Accounting\Payment\UI\ShowPayment;
use App\Actions\Accounting\PaymentAccount\ExportPaymentAccounts;
use App\Actions\Accounting\PaymentAccount\UI\CreatePaymentAccount;
use App\Actions\Accounting\PaymentAccount\UI\EditPaymentAccount;
use App\Actions\Accounting\PaymentAccount\UI\IndexPaymentAccounts;
use App\Actions\Accounting\PaymentAccount\UI\ShowPaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\ExportPaymentServiceProviders;
use App\Actions\Accounting\PaymentServiceProvider\UI\CreatePaymentServiceProvider;
use App\Actions\Accounting\PaymentServiceProvider\UI\EditPaymentServiceProvider;
use App\Actions\Accounting\PaymentServiceProvider\UI\RemovePaymentServiceProvider;
use App\Actions\Accounting\UI\IndexCustomerBalances;
use App\Actions\UI\Accounting\ShowAccountingDashboard;
use App\Stubs\UIDummies\IndexDummies;
use Illuminate\Support\Facades\Route;

Route::get('/', [ShowAccountingDashboard::class, 'inOrganisation'])->name('dashboard');

Route::get('/providers/{orgPaymentServiceProvider}/accounts/{paymentAccount}/payments/create', [IndexPayments::class, 'inPaymentAccountInPaymentServiceProvider'])->name('org-payment-service-providers.show.payment-accounts.show.payments.create');
Route::get('/providers/{orgPaymentServiceProvider}/payments/create', [IndexPayments::class, 'inPaymentServiceProvider'])->name('org-payment-service-providers.show.payments.create');


Route::get('/providers', SelectOrgPaymentServiceProviders::class)->name('org-payment-service-providers.index');
Route::get('/providers/create', CreatePaymentServiceProvider::class)->name('org-payment-service-providers.create');
Route::get('/providers/export', ExportPaymentServiceProviders::class)->name('org-payment-service-providers.export');
Route::get('/providers/{orgPaymentServiceProvider}', ShowOrgPaymentServiceProvider::class)->name('org-payment-service-providers.show');
Route::get('/providers/{orgPaymentServiceProvider}/edit', EditPaymentServiceProvider::class)->name('org-payment-service-providers.edit');
Route::get('/providers/{orgPaymentServiceProvider}/delete', RemovePaymentServiceProvider::class)->name('org-payment-service-providers.remove');
Route::get('/providers/{orgPaymentServiceProvider}/accounts', [IndexPaymentAccounts::class, 'inOrgPaymentServiceProvider'])->name('org-payment-service-providers.show.payment-accounts.index');
Route::get('/providers/{orgPaymentServiceProvider}/accounts/{paymentAccount}', [ShowPaymentAccount::class, 'inPaymentServiceProvider'])->name('org-payment-service-providers.show.payment-accounts.show');
Route::get('/providers/{orgPaymentServiceProvider}/accounts/{paymentAccount}/edit', [EditPaymentAccount::class, 'inPaymentServiceProvider'])->name('org-payment-service-providers.show.payment-accounts.edit');
Route::get('/providers/{orgPaymentServiceProvider}/accounts/{paymentAccount}/payments', [IndexPayments::class, 'inPaymentAccountInPaymentServiceProvider'])->name('org-payment-service-providers.show.payment-accounts.show.payments.index');
Route::get('/providers/{orgPaymentServiceProvider}/accounts/{paymentAccount}/payments/{payment}', [ShowPayment::class, 'inPaymentAccountInPaymentServiceProvider'])->name('org-payment-service-providers.show.payment-accounts.show.payments.show');
Route::get('/providers/{orgPaymentServiceProvider}/accounts/{paymentAccount}/payments/{payment}/edit', [EditPayment::class, 'inPaymentAccountInPaymentServiceProvider'])->name('org-payment-service-providers.show.payment-accounts.show.payments.edit');
Route::get('/providers/{orgPaymentServiceProvider}/payments', [IndexPayments::class, 'inPaymentServiceProvider'])->name('org-payment-service-providers.show.payments.index');
Route::get('/providers/{orgPaymentServiceProvider}/payments/{payment}/edit', [EditPayment::class, 'inPaymentServiceProvider'])->name('org-payment-service-providers.show.payments.edit');
Route::get('/providers/{orgPaymentServiceProvider}/payments/{payment}', [ShowPayment::class, 'inPaymentServiceProvider'])->name('org-payment-service-providers.show.payments.show');


Route::get('/accounts/{paymentAccount}/edit', EditPaymentAccount::class)->name('payment-accounts.edit');

Route::get('/accounts/create', CreatePaymentAccount::class)->name('payment-accounts.create');
Route::get('/accounts/export', ExportPaymentAccounts::class)->name('payment-accounts.export');
Route::get('/accounts/{paymentAccount}/payments/create', [CreatePayment::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.create');
Route::get('/payments/create', CreatePayment::class)->name('payments.create');
Route::get('/accounts', [IndexPaymentAccounts::class, 'inOrganisation'])->name('payment-accounts.index');
Route::get('/customer-balances', [IndexCustomerBalances::class, 'inOrganisation'])->name('customer-balances.index');
Route::get('/accounts/{paymentAccount}', [ShowPaymentAccount::class, 'inOrganisation'])->name('payment-accounts.show');
Route::get('/accounts/{paymentAccount}/payments', [IndexPayments::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.index');
Route::get('/accounts/{paymentAccount}/payments/{payment}', [ShowPayment::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.show');
Route::get('/accounts/{paymentAccount}/payments/{payment}/edit', [EditPayment::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.edit');
Route::get('/payments/export', ExportPayments::class)->name('payments.export');
Route::get('/payments', [IndexPayments::class, 'inOrganisation'])->name('payments.index');
Route::get('/payments/{payment}', [ShowPayment::class, 'inOrganisation'])->name('payments.show');
Route::get('/payments/{payment}/edit', [EditPayment::class, 'inOrganisation'])->name('payments.edit');
Route::get('/invoices/{invoice}/export', PdfInvoice::class)->name('invoices.download');
Route::get('/invoices/export', ExportInvoices::class)->name('invoices.export');
Route::get('/invoices', [IndexInvoices::class, 'inOrganisation'])->name('invoices.index');
Route::get('/invoices/{invoice}', [ShowInvoice::class, 'inOrganisation'])->name('invoices.show');

Route::get('/customer-balances', [IndexDummies::class, 'inOrganisation'])->name('balances.index');
