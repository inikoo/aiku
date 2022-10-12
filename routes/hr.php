<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 03:36:03 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Actions\HumanResources\Employee\CreateUserFromEmployee;
use App\Actions\HumanResources\Employee\IndexEmployee;
use App\Actions\HumanResources\Employee\ShowEmployee;
use App\Actions\HumanResources\ShowHumanResourcesDashboard;


Route::get('/', ShowHumanResourcesDashboard::class)->name('dashboard');
Route::get('/employees', IndexEmployee::class)->name('employees.index');
Route::get('/employees/{employee}', ShowEmployee::class)->name('employees.show');
Route::post('/employees/{employee}/user', ShowEmployee::class)->name('employees.show.user');
Route::post('/employees/{employee}/user', CreateUserFromEmployee::class)->name('employees.show.user.store');




//Route::get('/positions', IndexJobPosition::class)->name('job-positions.index');
//Route::get('/positions/{jobPosition}', ShowJobPosition::class)->name('employees.show');
