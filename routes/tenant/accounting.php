<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:42:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Accounting\Invoice\ExportInvoice;
use App\Actions\Accounting\Invoice\ExportInvoices;
use App\Actions\Accounting\Invoice\IndexInvoices;
use App\Actions\Accounting\Invoice\ShowInvoice;
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
use App\Actions\Accounting\PaymentServiceProvider\UI\IndexPaymentServiceProviders;
use App\Actions\Accounting\PaymentServiceProvider\UI\RemovePaymentServiceProvider;
use App\Actions\Accounting\PaymentServiceProvider\UI\ShowPaymentServiceProvider;
use App\Actions\UI\Accounting\AccountingDashboard;
use Illuminate\Support\Facades\Route;

if (empty($parent)) {
    $parent = 'tenant';
}


Route::get('/', [AccountingDashboard::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('dashboard');

if ($parent == 'tenant') {
    Route::get('/providers/{paymentServiceProvider}/accounts/{paymentAccount}/payments/create', [IndexPayments::class, 'inPaymentAccountInPaymentServiceProvider'])->name('payment-service-providers.show.payment-accounts.show.payments.create');
    Route::get('/providers/{paymentServiceProvider}/payments/create', [IndexPayments::class, 'inPaymentServiceProvider'])->name('payment-service-providers.show.payments.create');



    Route::get('/providers', IndexPaymentServiceProviders::class)->name('payment-service-providers.index');
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
}
Route::get('/accounts/create', CreatePaymentAccount::class)->name('payment-accounts.create');
Route::get('/accounts/export', ExportPaymentAccounts::class)->name('payment-accounts.export');
Route::get('/accounts/{paymentAccount}/payments/create', [CreatePayment::class, 'inPaymentAccount'])->name('payment-accounts.show.payments.create');
Route::get('/payments/create', CreatePayment::class)->name('payments.create');


Route::get('/accounts', [IndexPaymentAccounts::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('payment-accounts.index');
Route::get('/accounts/{paymentAccount}', [ShowPaymentAccount::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('payment-accounts.show');
Route::get('/accounts/{paymentAccount}/payments', [IndexPayments::class, $parent == 'tenant' ? 'inPaymentAccount' : 'inPaymentAccountInShop'])->name('payment-accounts.show.payments.index');
Route::get('/accounts/{paymentAccount}/payments/{payment}', [ShowPayment::class, $parent == 'tenant' ? 'inPaymentAccount' : 'inPaymentAccountInShop'])->name('payment-accounts.show.payments.show');
Route::get('/accounts/{paymentAccount}/payments/{payment}/edit', [EditPayment::class, $parent == 'tenant' ? 'inPaymentAccount' : 'inPaymentAccountInShop'])->name('payment-accounts.show.payments.edit');


Route::get('/payments/export', ExportPayments::class)->name('payments.export');

Route::get('/payments', [IndexPayments::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('payments.index');
Route::get('/payments/{payment}', [ShowPayment::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('payments.show');
Route::get('/payments/{payment}/edit', [EditPayment::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('payments.edit');

Route::get('/invoices/{invoice}/export', ExportInvoice::class)->name('invoices.download');
Route::get('/invoices/export', ExportInvoices::class)->name('invoices.export');

Route::get('/invoices', [IndexInvoices::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('invoices.index');
Route::get('/invoices/{invoice}', [ShowInvoice::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('invoices.show');
