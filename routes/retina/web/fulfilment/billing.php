<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 Jan 2025 12:37:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

use App\Actions\Accounting\Invoice\PdfInvoice;
use App\Actions\Retina\Billing\IndexInvoices;
use App\Actions\Retina\Billing\ShowInvoice;
use App\Actions\Retina\Storage\RecurringBill\UI\ShowRetinaCurrentRecurringBill;
use App\Actions\UI\Retina\Billing\UI\ShowRetinaBillingDashboard;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'dashboard');

Route::get('/dashboard', ShowRetinaBillingDashboard::class)->name('dashboard');

Route::get('next-bill', ShowRetinaCurrentRecurringBill::class)->name('next_recurring_bill');


Route::prefix('invoices')->as('invoices.')->group(function () {
    Route::get('/', [IndexInvoices::class, 'inRetina'])->name('index');
    Route::get('{invoice}', [ShowInvoice::class, 'inRetina'])->name('show');
    Route::get('/{invoice}/export', [PdfInvoice::class, 'inRetina'])->name('download');
});
