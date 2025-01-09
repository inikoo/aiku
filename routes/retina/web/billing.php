<?php

/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 07 Mar 2023 11:12:51 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

use App\Actions\Retina\Billing\IndexInvoices;
use App\Actions\Retina\Billing\ShowInvoice;
use App\Actions\Retina\Storage\RecurringBill\UI\IndexRecurringBills;
use App\Actions\Retina\Storage\RecurringBill\UI\ShowRecurringBill;
use App\Actions\UI\Retina\Billing\UI\ShowRetinaBillingDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', ShowRetinaBillingDashboard::class)->name('dashboard');

Route::prefix('recurring')->as('recurring.')->group(function () {
    Route::get('/', IndexRecurringBills::class)->name('index');
    Route::get('{recurringBill}', ShowRecurringBill::class)->name('show');
});

Route::prefix('invoices')->as('invoices.')->group(function () {
    Route::get('/', [IndexInvoices::class, 'inRetina'])->name('index');
    Route::get('{invoice}', [ShowInvoice::class, 'inRetina'])->name('show');
});
