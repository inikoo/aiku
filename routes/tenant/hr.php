<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:46:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\HumanResources\Employee\CreateUserFromEmployee;
use App\Actions\HumanResources\Employee\UI\CreateEmployee;
use App\Actions\HumanResources\Employee\UI\EditEmployee;
use App\Actions\HumanResources\Employee\UI\IndexEmployees;
use App\Actions\HumanResources\Employee\UI\ShowEmployee;
use App\Actions\HumanResources\JobPosition\IndexJobPosition;
use App\Actions\UI\ComingSoon;
use App\Actions\UI\HumanResources\HumanResourcesDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', HumanResourcesDashboard::class)->name('dashboard');
Route::get('/employees', IndexEmployees::class)->name('employees.index');
Route::get('/employees/create', CreateEmployee::class)->name('employees.create');

Route::get('/employees/{employee}', ShowEmployee::class)->name('employees.show');
Route::get('/employees/{employee}/edit', EditEmployee::class)->name('employees.edit');

Route::post('/employees/{employee}/user', ShowEmployee::class)->name('employees.show.user');
Route::post('/employees/{employee}/user', CreateUserFromEmployee::class)->name('employees.show.user.store');




Route::get('/positions', IndexJobPosition::class)->name('job-positions.index');
//Route::get('/positions/{jobPosition}', ShowJobPosition::class)->name('employees.show');

Route::get('/calendar', ComingSoon::class)->name('calendar');
Route::get('/time-sheets', ComingSoon::class)->name('time-sheets.hub');
Route::get('/clocking-machines', ComingSoon::class)->name('clocking-machines.index');
