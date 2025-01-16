<?php

/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 07 Mar 2023 11:12:51 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

use App\Actions\Accounting\Invoice\PdfInvoice;
use App\Actions\Retina\Billing\IndexInvoices;
use App\Actions\Retina\Billing\ShowInvoice;
use App\Actions\Retina\Storage\RecurringBill\UI\ShowRetinaCurrentRecurringBill;
use App\Actions\UI\Retina\Billing\UI\ShowRetinaBillingDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', ShowRetinaBillingDashboard::class)->name('dashboard');

Route::get('next-bill', ShowRetinaCurrentRecurringBill::class)->name('next_recurring_bill');


Route::prefix('invoices')->as('invoices.')->group(function () {
    Route::get('/', [IndexInvoices::class, 'inRetina'])->name('index');
    Route::get('{invoice}', [ShowInvoice::class, 'inRetina'])->name('show');
    Route::get('/{invoice}/export', [PdfInvoice::class, 'inRetina'])->name('download');
});
