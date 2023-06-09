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
/*
Route::get('/calendars', IndexCalendars::class)->name('calendars.index');
Route::get('/calendars/{calendar}', ShowCalendar::class)->name('calendars.show');

Route::get('/time-sheets', IndexTimeSheets::class)->name('time-sheets.index');
Route::get('/time-sheets/{timeSheet}', ShowTimesheet::class)->name('time-sheets.show');

Route::get('/working-places', IndexWorkingPlaces::class)->name('working-places.index');
Route::get('/working-places/{workingPlace}', ShowWorkingPlace::class)->name('working-places.show');

Route::get('/clocking-machines', IndexClockingMachines::class)->name('clocking-machines.index');
Route::get('/clocking-machines/{clockingMachine}', ShowClockingMachine::class)->name('clocking-machines.show');

Route::get('/clocking', IndexClockings::class)->name('clockings.index');
Route::get('/clocking/{clocking}', ShowClocking::class)->name('clockings.show');

*/
