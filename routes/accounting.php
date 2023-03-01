<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 01 Mar 2023 14:51:52 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Accounting\Payment\IndexPayments;
use App\Actions\Accounting\PaymentAccount\IndexPaymentAccounts;
use App\Actions\Accounting\PaymentServiceProvider\IndexPaymentServiceProviders;
use App\Actions\Accounting\ShowAccountingDashboard;
use Illuminate\Support\Facades\Route;


Route::get('/', ShowAccountingDashboard::class)->name('dashboard');

Route::get('/payment-service-providers', IndexPaymentServiceProviders::class)->name('payment-service-providers.index');

Route::get('/accounts', IndexPaymentAccounts::class)->name('accounts.index');

Route::get('/payments', IndexPayments::class)->name('payments.index');

