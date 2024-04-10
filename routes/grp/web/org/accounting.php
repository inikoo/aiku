<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 16 Mar 2024 20:36:21 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Accounting\Invoice\ExportInvoice;
use App\Actions\Accounting\Invoice\ExportInvoices;
use App\Actions\Accounting\Invoice\IndexInvoices;
use App\Actions\Accounting\Invoice\ShowInvoice;
use App\Actions\Accounting\OrgPaymentServiceProvider\UI\SelectOrgPaymentServiceProviders;
use App\Actions\Accounting\Payment\ExportPayments;
use App\Actions\Accounting\Payment\UI\CreatePayment;
use App\Actions\Accounting\Payment\UI\EditPayment;
use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\Accounting\Payment\UI\ShowPayment;
use App\Actions\Accounting\PaymentAccount\ExportPaymentAccounts;
use App\Actions\Accounting\PaymentAccount\UI\CreatePaymentAccount;
use App\Actions\Accounting\PaymentAccount\UI\IndexPaymentAccounts;
use App\Actions\Accounting\PaymentAccount\UI\ShowPaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\ExportPaymentServiceProviders;
use App\Actions\Accounting\PaymentServiceProvider\UI\CreatePaymentServiceProvider;
use App\Actions\Accounting\PaymentServiceProvider\UI\EditPaymentServiceProvider;
use App\Actions\Accounting\PaymentServiceProvider\UI\RemovePaymentServiceProvider;
use App\Actions\Accounting\PaymentServiceProvider\UI\ShowPaymentServiceProvider;
use App\Actions\UI\Accounting\ShowAccountingDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', [ShowAccountingDashboard::class, 'inOrganisation'])->name('dashboard');

Route::get('/providers/{paymentServiceProvider}/accounts/{paymentAccount}/payments/create', [IndexPayments::class, 'inPaymentAccountInPaymentServiceProvider'])->name('payment-service-providers.show.payment-accounts.show.payments.create');
Route::get('/providers/{paymentServiceProvider}/payments/create', [IndexPayments::class, 'inPaymentServiceProvider'])->name('payment-service-providers.show.payments.create');


Route::get('/providers', SelectOrgPaymentServiceProviders::class)->name('payment-service-providers.index');
Route::get('/providers/create', CreatePaymentServiceProvider::class)->name('payment-service-providers.create');
Route::get('/providers/export', ExportPaymentServiceProviders::class)->name('payment-service-providers.export');
Route::get('/providers/{paymentServiceProvider}', ShowPaymentServiceProvider::class)->name('payment-service-providers.show');
Route::get('/providers/{paymentServiceProvider}/edit', EditPaymentServiceProvider::class)->name('payment-service-providers.edit');
Route::get('/providers/{paymentServiceProvider}/delete', RemovePaymentServiceProvider::class)->name('payment-service-providers.remove');
Route::get('/providers/{paymentServiceProvider}/accounts', [IndexPaymentAccounts::class, 'inPaymentServiceProvider'])->name('payment-service-providers.show.payment-accounts.index');
Route::get('/providers/{paymentServiceProvider}/accounts/{paymentAccount}', [ShowPaymentAccount::class, 'inPaymentServiceProvider'])->name('payment-service-providers.show.payment-accounts.show');
Route::get('/providers/{paymentServiceProvider}/accounts/{paymentAccount}/payments', [IndexPayments::class, 'inPaymentAccountInPaymentServiceProvider'])->name('payment-service-providers.show.payment-accounts.show.payments.index');
Route::get('/providers/{paymentServiceProvider}/accounts/{paymentAccount}/payments/{payment}', [ShowPayment::class, 'inPaymentAccountInPaymentServiceProvider'])->name('payment-service-providers.show.payment-accounts.show.payments.show');
Route::get('/providers/{paymentServiceProvider}/accounts/{paymentAccount}/payments/{payment}/edit', [EditPayment::class, 'inPaymentAccountInPaymentServiceProvider'])->name('payment-service-providers.show.payment-accounts.show.payments.edit');
Route::get('/providers/{paymentServiceProvider}/payments', [IndexPayments::class, 'inPaymentServiceProvider'])->name('payment-service-providers.show.payments.index');
Route::get('/providers/{paymentServiceProvider}/payments/{payment}/edit', [EditPayment::class, 'inPaymentServiceProvider'])->name('payment-service-providers.show.payments.edit');
Route::get('/providers/{paymentServiceProvider}/payments/{payment}', [ShowPayment::class, 'inPaymentServiceProvider'])->name('payment-service-providers.show.payments.show');


Route::get('/accounts/create', CreatePaymentAccount::class)->name('payment-accounts.create');
Route::get('/accounts/export', ExportPaymentAccounts::class)->name('payment-accounts.export');
Route::get('/accounts/{paymentAccount}/payments/create', [CreatePayment::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.create');
Route::get('/payments/create', CreatePayment::class)->name('payments.create');
Route::get('/accounts', [IndexPaymentAccounts::class, 'inOrganisation'])->name('payment-accounts.index');
Route::get('/accounts/{paymentAccount}', [ShowPaymentAccount::class, 'inOrganisation'])->name('payment-accounts.show');
Route::get('/accounts/{paymentAccount}/payments', [IndexPayments::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.index');
Route::get('/accounts/{paymentAccount}/payments/{payment}', [ShowPayment::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.show');
Route::get('/accounts/{paymentAccount}/payments/{payment}/edit', [EditPayment::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.edit');
Route::get('/payments/export', ExportPayments::class)->name('payments.export');
Route::get('/payments', [IndexPayments::class, 'inOrganisation'])->name('payments.index');
Route::get('/payments/{payment}', [ShowPayment::class, 'inOrganisation'])->name('payments.show');
Route::get('/payments/{payment}/edit', [EditPayment::class, 'inOrganisation'])->name('payments.edit');
Route::get('/invoices/{invoice}/export', ExportInvoice::class)->name('invoices.download');
Route::get('/invoices/export', ExportInvoices::class)->name('invoices.export');
Route::get('/invoices', [IndexInvoices::class, 'inOrganisation'])->name('invoices.index');
Route::get('/invoices/{invoice}', [ShowInvoice::class, 'inOrganisation'])->name('invoices.show');
/*
Route::get('/shops/{shop}', ShowAccountingDashboard::class)->name('shops.show.dashboard');
Route::get('/shops/{shop}/accounts/export', [ExportPaymentAccounts::class,'inShop'])->name('shops.show.payment-accounts.export');
Route::get('/shops/{shop}/accounts/{paymentAccount}/payments/create', [CreatePayment::class, 'inPaymentAccountInShop'])->name('shops.show.payment-accounts.show.payments.create');
Route::get('/shops/{shop}/accounts', [IndexPaymentAccounts::class, 'inShop'])->name('shops.show.payment-accounts.index');
Route::get('/shops/{shop}/accounts/{paymentAccount}', [ShowPaymentAccount::class, 'inShop'])->name('shops.show.payment-accounts.show');
Route::get('/shops/{shop}/accounts/{paymentAccount}/payments', [IndexPayments::class, 'inPaymentAccountInShop'])->name('shops.show.payment-accounts.show.payments.index');
Route::get('/shops/{shop}/accounts/{paymentAccount}/payments/{payment}', [ShowPayment::class, 'inPaymentAccountInShop'])->name('shops.show.payment-accounts.show.payments.show');
Route::get('/shops/{shop}/accounts/{paymentAccount}/payments/{payment}/edit', [EditPayment::class, 'inPaymentAccountInShop'])->name('shops.show.payment-accounts.show.payments.edit');
Route::get('/shops/{shop}/payments/export', ExportPayments::class)->name('shops.show.payments.export');
Route::get('/shops/{shop}/payments', [IndexPayments::class, 'inShop'])->name('shops.show.payments.index');
Route::get('/shops/{shop}/payments/{payment}', [ShowPayment::class, 'inShop'])->name('shops.show.payments.show');
Route::get('/shops/{shop}/payments/{payment}/edit', [EditPayment::class, 'inShop'])->name('shops.show.payments.edit');
Route::get('/shops/{shop}/invoices/{invoice}/export', ExportInvoice::class)->name('shops.show.invoices.download');
Route::get('/shops/{shop}/invoices/export', ExportInvoices::class)->name('shops.show.invoices.export');
Route::get('/shops/{shop}/invoices', [IndexInvoices::class, 'inShop'])->name('shops.show.invoices.index');
Route::get('/shops/{shop}/invoices/{invoice}', [ShowInvoice::class, 'inShop'])->name('shops.show.invoices.show');
*/
