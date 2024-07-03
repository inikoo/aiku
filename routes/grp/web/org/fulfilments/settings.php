<?php

use App\Stubs\UIDummies\ShowDummyDashboard;
use Illuminate\Support\Facades\Route;

Route::get('', ShowDummyDashboard::class)->name('dashboard');
