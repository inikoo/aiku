<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:40:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Auth\Guest\ExportGuests;
use App\Actions\Auth\Guest\UI\CreateGuest;
use App\Actions\Auth\Guest\UI\EditGuest;
use App\Actions\Auth\Guest\UI\IndexGuest;
use App\Actions\Auth\Guest\UI\RemoveGuest;
use App\Actions\Auth\Guest\UI\ShowGuest;
use App\Actions\Auth\User\ExportUsers;
use App\Actions\Auth\User\UI\CreateUser;
use App\Actions\Auth\User\UI\EditUser;
use App\Actions\Auth\User\UI\IndexUsers;
use App\Actions\Auth\User\UI\ShowUser;
use App\Actions\UI\Dashboard\ShowDashTV;
use App\Actions\UI\SysAdmin\SysAdminDashboard;
use App\Actions\UI\SysAdmin\EditSystemSettings;
use Illuminate\Support\Facades\Route;

Route::get('/', [
    'uses'   => SysAdminDashboard::class,
     'icon'  => 'users-cog',
    'label'  => 'sysadmin'
])->name('dashboard');
Route::get('/settings', EditSystemSettings::class)->name('settings.edit');

Route::get('/users', IndexUsers::class)->name('users.index');
Route::get('/users/export', ExportUsers::class)->name('users.export');

Route::get('/users/create', CreateUser::class)->name('users.create');
Route::get('/users/{user}', ShowUser::class)->name('users.show');
Route::get('/users/{user}/edit', EditUser::class)->name('users.edit');

Route::get('/guests', IndexGuest::class)->name('guests.index');
Route::get('/guests/create', CreateGuest::class)->name('guests.create');
Route::get('/guests/export', ExportGuests::class)->name('guests.export');

Route::get('/guests/{guest}', ShowGuest::class)->name('guests.show');
Route::get('/guests/{guest}/edit', EditGuest::class)->name('guests.edit');
Route::get('/guests/{guest}/delete', RemoveGuest::class)->name('guests.remove');




Route::get('/dashtv', ShowDashTV::class)->name('dashtv');
