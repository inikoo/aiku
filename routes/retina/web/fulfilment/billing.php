<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 Jan 2025 12:37:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

use App\Actions\Retina\Accounting\RetinaPdfInvoice;
use App\Actions\Retina\Billing\RetinaIndexInvoices;
use App\Actions\Retina\Billing\RetinaShowInvoice;
use App\Actions\Retina\Storage\RecurringBill\UI\RetinaShowCurrentRecurringBill;
use App\Actions\UI\Retina\Billing\UI\RetinaShowRetinaBillingDashboard;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'dashboard');

Route::get('/dashboard', RetinaShowRetinaBillingDashboard::class)->name('dashboard');

Route::get('next-bill', RetinaShowCurrentRecurringBill::class)->name('next_recurring_bill');


Route::prefix('invoices')->as('invoices.')->group(function () {
    Route::get('/', RetinaIndexInvoices::class)->name('index');
    Route::get('{invoice}', RetinaShowInvoice::class)->name('show');
    Route::get('/{invoice}/export', RetinaPdfInvoice::class)->name('download');
});
