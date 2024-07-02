<?php

use App\Actions\Mail\Outbox\UI\IndexOutboxes;
use App\Actions\Mail\Outbox\UI\ShowOutbox;
use App\Actions\Mail\Outbox\UI\ShowOutboxDashboard;
use App\Actions\Mail\ShowMailDashboard;
use Illuminate\Support\Facades\Route;

Route::get('', ShowMailDashboard::class)->name('dashboard');
Route::get('outboxes', [IndexOutboxes::class, 'inShop'])->name('outboxes');
Route::get('outboxes/{outbox}', [ShowOutbox::class, 'inShop'])->name('outboxes.show');
Route::get('{website}/outboxes', [IndexOutboxes::class, 'inWebsite'])->name('website.outboxes');
