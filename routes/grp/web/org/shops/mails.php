<?php

use App\Actions\Mail\Outbox\UI\IndexOutboxes;
use App\Actions\Mail\ShowMailDashboard;
use Illuminate\Support\Facades\Route;

Route::get('', ShowMailDashboard::class)->name('dashboard');
Route::get('outboxes', [IndexOutboxes::class, 'inShop'])->name('outboxes');
Route::get('{website}/outboxes', [IndexOutboxes::class, 'inWebsite'])->name('website.outboxes');
