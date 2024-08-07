<?php

use App\Actions\Mail\Outbox\UI\IndexOutboxes;
use App\Actions\Mail\Outbox\UI\ShowOutbox;
use App\Actions\Mail\Outbox\UI\ShowOutboxWorkshop;
use App\Actions\Mail\ShowMailDashboard;
use Illuminate\Support\Facades\Route;

Route::get('', ShowMailDashboard::class)->name('dashboard');
Route::get('outboxes', [IndexOutboxes::class, 'inShop'])->name('outboxes');
Route::get('outboxes/{outbox}', [ShowOutbox::class, 'inShop'])->name('outboxes.show');
Route::get('outboxes/{outbox}/workshop', ShowOutboxWorkshop::class)->name('outboxes.workshop');
