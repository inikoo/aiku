<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:46:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\HumanResources\Employee\CreateUserFromEmployee;
use App\Actions\HumanResources\Employee\IndexEmployee;
use App\Actions\HumanResources\Employee\ShowEmployee;
use App\Actions\UI\HumanResources\HumanResourcesDashboard;

Route::get('/', HumanResourcesDashboard::class)->name('dashboard');
Route::get('/employees', IndexEmployee::class)->name('employees.index');
Route::get('/employees/{employee}', ShowEmployee::class)->name('employees.show');
Route::post('/employees/{employee}/user', ShowEmployee::class)->name('employees.show.user');
Route::post('/employees/{employee}/user', CreateUserFromEmployee::class)->name('employees.show.user.store');




//Route::get('/positions', IndexJobPosition::class)->name('job-positions.index');
//Route::get('/positions/{jobPosition}', ShowJobPosition::class)->name('employees.show');
