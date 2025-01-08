<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Feb 2024 16:50:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Fulfilment\FulfilmentCustomer\IndexFulfilmentCustomerFromWebhook;
use App\Actions\UI\Iris\Appointment\ShowPublicAppointment;
use Illuminate\Support\Facades\Route;
use App\Actions\CRM\WebUser\Retina\RetinaLogin;
use Inertia\Inertia;

Route::get('/login', function () {return Inertia::render('RetinaLogin');})->name('login');
Route::post('login', RetinaLogin::class)->name('login.store');

Route::get('/register', function () {return Inertia::render('Register');})->name('register');

Route::get('webhooks/{fulfilmentCustomer:webhook_access_key}', IndexFulfilmentCustomerFromWebhook::class)->name('fulfilment-customer.webhook.show');

Route::get('/appointment', ShowPublicAppointment::class)->name('.appointment');

Route::prefix("disclosure")->name("disclosure.")->group(__DIR__."/disclosure.php");

Route::prefix("crm")->name("crm.")->group(__DIR__."/crm.php");

Route::prefix("unsubscribe")->name("unsubscribe.")->group(__DIR__."/unsubscribe.php");
