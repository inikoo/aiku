<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 11 Aug 2023 09:22:13 Malaysia Time, Pantai Lembeng, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('.welcome');

Route::get('/contact', function () {
    return Inertia::render('Contact');
})->name('.contact');

Route::get('/about', function () {
    return Inertia::render('About');
})->name('.about');

Route::get('/tnc', function () {
    return Inertia::render('Tnc');
})->name('.tnc');

Route::get('/storage', function () {
    return Inertia::render('Storage');
})->name('.storage');

Route::get('/pick_pack', function () {
    return Inertia::render('Pick_pack');
})->name('.pick_pack');

Route::get('/rework', function () {
    return Inertia::render('Rework');
})->name('.rework');

Route::get('/pricing', function () {
    return Inertia::render('Pricing');
})->name('.pricing');

Route::get('/shipping', function () {
    return Inertia::render('Shipping');
})->name('.shipping');
