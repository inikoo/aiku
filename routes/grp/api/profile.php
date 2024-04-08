<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 28 Dec 2023 23:41:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\UI\Notification\IndexNotification;
use App\Actions\UI\Notification\ReadNotification;
use App\Actions\UI\Notification\ShowNotification;
use App\Actions\UI\Profile\ShowProfile;
use App\Actions\UI\Profile\UpdateProfile;

Route::get('/', ShowProfile::class)->name('show');
Route::post('/', UpdateProfile::class)->name('update');

Route::get('/notifications', IndexNotification::class)->name('notifications.index');
Route::get('/notifications/{notification}', ShowNotification::class)->name('notifications.show');
Route::patch('/notifications/{notification}', ReadNotification::class)->name('notifications.read');
