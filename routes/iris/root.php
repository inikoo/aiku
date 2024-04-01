<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Feb 2024 16:50:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Fulfilment\FulfilmentCustomer\IndexFulfilmentCustomerFromWebhook;
use App\Actions\UI\Iris\Appointment\ShowPublicAppointment;
use Inertia\Inertia;
use App\Actions\UI\Iris\ShowHome;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowHome::class)->name('home');

Route::get('webhooks/{fulfilmentCustomer:webhook_access_key}', IndexFulfilmentCustomerFromWebhook::class)->name('fulfilment-customer.webhook.show');

Route::prefix("disclosure")
    ->name("disclosure.")
    ->group(__DIR__."/disclosure.php");

Route::get('/appointment', ShowPublicAppointment::class)->name('.appointment');

Route::prefix("crm")
    ->name("crm.")
    ->group(__DIR__."/crm.php");


/*

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
*/
