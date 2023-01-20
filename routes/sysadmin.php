<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 21:58:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Dashboard\DisplayDashTV;
use App\Actions\SysAdmin\Guest\IndexGuest;
use App\Actions\SysAdmin\Guest\ShowGuest;
use App\Actions\SysAdmin\ShowSysAdminDashboard;
use App\Actions\SysAdmin\User\IndexUser;
use App\Actions\SysAdmin\User\ShowUser;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowSysAdminDashboard::class)->name('dashboard');

Route::get('/users', IndexUser::class)->name('users.index');
Route::get('/users/{user}', ShowUser::class)->name('users.show');
Route::get('/guests', IndexGuest::class)->name('guests.index');
Route::get('/guests/{guest}', ShowGuest::class)->name('guests.show');
Route::get('/dashtv', DisplayDashTV::class)->name('dashtv');

