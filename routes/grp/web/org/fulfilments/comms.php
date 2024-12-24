<?php

use App\Actions\Comms\Outbox\UI\IndexOutboxes;
use App\Actions\Comms\Outbox\UI\ShowOutbox;
use App\Actions\Comms\UI\ShowCommsDashboard;
use Illuminate\Support\Facades\Route;

Route::get('', [ShowCommsDashboard::class, 'inFulfilment'])->name('dashboard');
Route::get('outboxes', [IndexOutboxes::class, 'inFulfilment'])->name('outboxes');
Route::get('outboxes/{outbox}', [ShowOutbox::class, 'inFulfilment'])->name('outboxes.show');
