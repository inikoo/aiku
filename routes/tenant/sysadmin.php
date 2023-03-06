<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:40:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\SysAdmin\Guest\IndexGuest;
use App\Actions\SysAdmin\Guest\ShowGuest;
use App\Actions\SysAdmin\User\IndexUser;
use App\Actions\SysAdmin\User\ShowUser;
use App\Actions\UI\Dashboard\DashTV;
use App\Actions\UI\SysAdmin\SysAdminDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', SysAdminDashboard::class)->name('dashboard');

Route::get('/users', IndexUser::class)->name('users.index');
Route::get('/users/{user}', ShowUser::class)->name('users.show');
Route::get('/guests', IndexGuest::class)->name('guests.index');
Route::get('/guests/{guest}', ShowGuest::class)->name('guests.show');
Route::get('/dashtv', DashTV::class)->name('dashtv');
