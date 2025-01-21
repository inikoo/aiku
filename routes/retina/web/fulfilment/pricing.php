<?php
/*
 * author Arya Permana - Kirin
 * created on 21-01-2025-10h-53m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

use App\Actions\Retina\Fulfilment\RecurringBill\UI\ShowRetinaCurrentRecurringBill;
use App\Actions\Retina\Fulfilment\UI\IndexRetinaPricing;
use App\Actions\Retina\Pricing\UI\IndexRetinaGoods;
use App\Actions\Retina\Pricing\UI\IndexRetinaRentals;
use App\Actions\Retina\Pricing\UI\IndexRetinaServices;
use Illuminate\Support\Facades\Route;


Route::redirect('/', 'dashboard');

Route::get('', IndexRetinaPricing::class);
Route::get('/services', IndexRetinaServices::class)->name('.services');
Route::get('/rentals', IndexRetinaRentals::class)->name('.rentals');
Route::get('/goods', IndexRetinaGoods::class)->name('.goods');
