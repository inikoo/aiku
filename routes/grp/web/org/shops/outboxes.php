<?php

use App\Actions\Mail\Outbox\UI\ShowOutboxDashboard;
use Illuminate\Support\Facades\Route;

Route::get('', ShowOutboxDashboard::class)->name('dashboard');