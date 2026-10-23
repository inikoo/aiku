<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 13 Jul 2024 00:38:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Shop\UI\EditShop;
use App\Actions\Comms\Mailbox\ConnectShopMailbox;
use App\Actions\Comms\Mailbox\DisconnectShopMailbox;
use App\Actions\CRM\Appointment\GoogleCalendar\ConnectShopGoogleCalendar;
use App\Actions\CRM\Appointment\GoogleCalendar\DisconnectShopGoogleCalendar;
use App\Actions\CRM\Appointment\GoogleCalendar\ReconcileShopGoogleCalendar;
use Illuminate\Support\Facades\Route;

Route::get('', EditShop::class)->name('edit');
Route::get('mailbox/connect', ConnectShopMailbox::class)->name('mailbox.connect');
Route::post('mailbox/disconnect', DisconnectShopMailbox::class)->name('mailbox.disconnect');
Route::get('google-calendar/connect', ConnectShopGoogleCalendar::class)->name('google_calendar.connect');
Route::post('google-calendar/disconnect', DisconnectShopGoogleCalendar::class)->name('google_calendar.disconnect');
Route::post('google-calendar/sync', ReconcileShopGoogleCalendar::class)->name('google_calendar.sync');
