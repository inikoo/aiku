<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 Jan 2025 12:37:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

use App\Actions\Retina\Accounting\RetinaPdfInvoice;
use App\Actions\Retina\Billing\IndexRetinaInvoices;
use App\Actions\Retina\Billing\ShowRetinaInvoice;
use App\Actions\Retina\Storage\RecurringBill\UI\ShowRetinaCurrentRecurringBill;
use App\Actions\UI\Retina\Billing\UI\ShowRetinaRetinaBillingDashboard;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'dashboard');

Route::get('/dashboard', ShowRetinaRetinaBillingDashboard::class)->name('dashboard');

Route::get('next-bill', ShowRetinaCurrentRecurringBill::class)->name('next_recurring_bill');

Route::prefix('invoices')->as('invoices.')->group(function () {
    Route::get('/', IndexRetinaInvoices::class)->name('index');
    Route::get('{invoice}', ShowRetinaInvoice::class)->name('show');
    Route::get('/{invoice}/export', RetinaPdfInvoice::class)->name('download');
});
