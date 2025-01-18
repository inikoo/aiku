<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 18 Jan 2025 03:58:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

use App\Actions\UI\Retina\SysAdmin\CreateRetinaWebUser;
use App\Actions\UI\Retina\SysAdmin\EditRetinaWebUser;
use App\Actions\UI\Retina\SysAdmin\IndexRetinaWebUsers;
use App\Actions\UI\Retina\SysAdmin\ShowRetinaAccountManagement;
use App\Actions\UI\Retina\SysAdmin\ShowRetinaSysAdminDashboard;
use App\Actions\UI\Retina\SysAdmin\ShowRetinaWebUser;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowRetinaSysAdminDashboard::class)->name('dashboard');
Route::get('/settings', ShowRetinaAccountManagement::class)->name('settings.edit');
Route::get('/users', IndexRetinaWebUsers::class)->name('web-users.index');
Route::get('/users/create', CreateRetinaWebUser::class)->name('web-users.create');
Route::get('/users/{webUser}', ShowRetinaWebUser::class)->name('web-users.show');
Route::get('/users/{webUser}/edit', EditRetinaWebUser::class)->name('web-users.edit');
