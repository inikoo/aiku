<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Feb 2024 16:50:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Fulfilment\FulfilmentCustomer\IndexFulfilmentCustomerFromWebhook;
use App\Actions\UI\Iris\Appointment\ShowPublicAppointment;
use App\Actions\UI\Iris\ShowHome;
use App\Actions\UI\Iris\ShowAbout;
use App\Actions\UI\Iris\ShowContact;
use App\Actions\UI\Iris\ShowPickpack;
use App\Actions\UI\Iris\ShowPricing;
use App\Actions\UI\Iris\ShowRework;
use App\Actions\UI\Iris\ShowShipping;
use App\Actions\UI\Iris\ShowStorage;
use App\Actions\UI\Iris\ShowTnc;
use App\Actions\UI\Iris\ShowWelcome;
use App\Actions\UI\Iris\ShowSearchResult;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowHome::class)->name('home');
Route::get('/about', ShowAbout::class)->name('about');
Route::get('/contact', ShowContact::class)->name('contact');
Route::get('/pick_pack', ShowPickpack::class)->name('pickpack');
Route::get('/pricing', ShowPricing::class)->name('pricing');
Route::get('/rework', ShowRework::class)->name('rework');
Route::get('/shipping', ShowShipping::class)->name('shipping');
Route::get('/storage', ShowStorage::class)->name('storage');
Route::get('/tnc', ShowTnc::class)->name('tnc');
Route::get('/welcome', ShowWelcome::class)->name('welcome');

Route::get('/search', ShowSearchResult::class)->name('search');

Route::get('webhooks/{fulfilmentCustomer:webhook_access_key}', IndexFulfilmentCustomerFromWebhook::class)->name('fulfilment-customer.webhook.show');

Route::prefix("disclosure")
    ->name("disclosure.")
    ->group(__DIR__."/disclosure.php");

Route::get('/appointment', ShowPublicAppointment::class)->name('.appointment');

Route::prefix("crm")
    ->name("crm.")
    ->group(__DIR__."/crm.php");
