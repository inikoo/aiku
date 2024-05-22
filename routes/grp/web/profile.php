<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 09 Sept 2022 18:32:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Actions\HumanResources\Timesheet\UI\IndexTimesheets;
use App\Actions\UI\Notification\IndexNotification;
use App\Actions\UI\Profile\EditProfile;
use App\Actions\UI\Profile\ShowProfile;
use App\Actions\UI\Profile\ShowProfileIndexHistory;
use App\Actions\UI\Profile\ShowProfileIndexVisitLogs;
use App\Actions\UI\Profile\ShowProfilePageHeadTabs;
use App\Actions\UI\Profile\ShowProfileShowcase;
use App\Actions\UI\Profile\UpdateProfile;

Route::get('/', ShowProfile::class)->name('show');
Route::get('/edit', EditProfile::class)->name('edit');
Route::post('/', UpdateProfile::class)->name('update');

Route::get('/notifications', IndexNotification::class)->name('notifications.index');

Route::get('/page-head-tabs', ShowProfilePageHeadTabs::class)->name('page-head-tabs.show');
Route::get('/showcase', ShowProfileShowcase::class)->name('showcase.show');
Route::get('/timesheets', IndexTimesheets::class)->name('timesheets.index');
Route::get('/histories', ShowProfileIndexHistory::class)->name('history.index');
Route::get('/visit-logs', ShowProfileIndexVisitLogs::class)->name('visit-logs.index');
