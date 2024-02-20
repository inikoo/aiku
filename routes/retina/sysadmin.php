<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Feb 2024 20:14:26 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\SysAdmin\User\UI\CreateUser;
use App\Actions\SysAdmin\User\UI\EditUser;
use App\Actions\SysAdmin\User\UI\IndexUsers;
use App\Actions\SysAdmin\User\UI\ShowUser;
use App\Actions\UI\Grp\SysAdmin\EditSystemSettings;
use App\Actions\UI\Retina\SysAdmin\ShowSysAdminDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowSysAdminDashboard::class)->name('dashboard');
Route::get('/settings', EditSystemSettings::class)->name('settings.edit');
Route::get('/users', IndexUsers::class)->name('web-users.index');
Route::get('/users/create', CreateUser::class)->name('web-users.create');
Route::get('/users/{user}', ShowUser::class)->name('web-users.show');
Route::get('/users/{user}/edit', EditUser::class)->name('web-users.edit');
