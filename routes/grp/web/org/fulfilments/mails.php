<?php

use App\Actions\Mail\Outbox\UI\IndexOutboxes;
use App\Actions\Mail\Outbox\UI\ShowOutbox;
use App\Actions\Mail\ShowMailDashboard;
use Illuminate\Support\Facades\Route;

Route::get('', [ShowMailDashboard::class, 'inFulfilment'])->name('dashboard');
Route::get('outboxes', [IndexOutboxes::class, 'inFulfilment'])->name('outboxes');
Route::get('outboxes/{outbox}', [ShowOutbox::class, 'inFulfilment'])->name('outboxes.show');