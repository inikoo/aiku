<?php

use App\Actions\Fulfilment\RecurringBill\UI\ShowRecurringBillSetting;
use App\Actions\Fulfilment\Setting\ShowFulfilmentSettingDashboard;
use App\Actions\Mail\Outbox\UI\IndexOutboxes;
use App\Actions\Mail\Outbox\UI\ShowOutbox;
use Illuminate\Support\Facades\Route;

Route::get('', ShowFulfilmentSettingDashboard::class)->name('dashboard');

Route::prefix('ouboxes')->as('outboxes.')->group(function () {
    Route::get('', [IndexOutboxes::class, 'inFulfilment'])->name('index');
    Route::get('{outbox}', [ShowOutbox::class, 'inFulfilment'])->name('show');
});
