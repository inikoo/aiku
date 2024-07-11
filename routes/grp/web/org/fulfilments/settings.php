<?php

use App\Actions\Fulfilment\RecurringBill\UI\ShowRecurringBillSetting;
use App\Actions\Fulfilment\Setting\ShowFulfilmentSettingDashboard;
use App\Actions\Mail\Outbox\UI\IndexOutboxes;
use App\Actions\Mail\Outbox\UI\ShowOutbox;
use App\Stubs\UIDummies\ShowDummyDashboard;
use Illuminate\Support\Facades\Route;

Route::get('', ShowFulfilmentSettingDashboard::class)->name('dashboard');

Route::prefix('ouboxes')->as('outboxes.')->group(function () {
    Route::get('', [IndexOutboxes::class, 'inFulfilment'])->name('index');
    Route::get('{outbox}', [ShowOutbox::class, 'inFulfilment'])->name('show');
});
Route::prefix('recurring-bill')->as('recurring-bill.')->group(function () {
    Route::get('edit', ShowRecurringBillSetting::class)->name('edit');
});
