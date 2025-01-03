<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:40:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Analytics\UserRequest\UI\IndexUserRequestLogs;
use App\Actions\SysAdmin\Group\UI\EditGroupSettings;
use App\Actions\SysAdmin\Guest\ExportGuests;
use App\Actions\SysAdmin\Guest\UI\CreateGuest;
use App\Actions\SysAdmin\Guest\UI\EditGuest;
use App\Actions\SysAdmin\Guest\UI\IndexGuests;
use App\Actions\SysAdmin\Guest\UI\ShowGuest;
use App\Actions\SysAdmin\User\ExportUsers;
use App\Actions\SysAdmin\User\UI\CreateUser;
use App\Actions\SysAdmin\User\UI\EditUser;
use App\Actions\SysAdmin\User\UI\IndexUserActions;
use App\Actions\SysAdmin\User\UI\IndexUsers;
use App\Actions\SysAdmin\User\UI\ShowUser;
use App\Actions\UI\Grp\SysAdmin\ShowSysAdminDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowSysAdminDashboard::class)->name('dashboard');
Route::get('/settings', EditGroupSettings::class)->name('settings.edit');

Route::prefix('users')->as('users.')->group(function () {
    Route::get('/active', IndexUsers::class)->name('index');
    Route::get('/suspended', [IndexUsers::class, 'inSuspended'])->name('suspended.index');
    Route::get('/all', [IndexUsers::class, 'inAll'])->name('all.index');
    Route::get('/requests', IndexUserRequestLogs::class)->name('request.index');
});
Route::get('/users/export', ExportUsers::class)->name('users.export');

Route::get('/users/create', CreateUser::class)->name('users.create');
Route::get('/users/{user}', ShowUser::class)->name('users.show');
Route::get('/users/{user}/action', IndexUserActions::class)->name('users.show.actions.index');
Route::get('/users/{user}/edit', EditUser::class)->name('users.edit');

Route::get('/guests', IndexGuests::class)->name('guests.index');
Route::get('/guests/create', CreateGuest::class)->name('guests.create');
Route::get('/guests/export', ExportGuests::class)->name('guests.export');

Route::get('/guests/{guest}', ShowGuest::class)->name('guests.show');
Route::get('/guests/{guest}/edit', EditGuest::class)->name('guests.edit');
