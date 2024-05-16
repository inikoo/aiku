<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 30 Dec 2023 15:00:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\HumanResources\Calendar\IndexCalendars;
use App\Actions\HumanResources\Calendar\ShowCalendar;
use App\Actions\HumanResources\Clocking\UI\CreateClocking;
use App\Actions\HumanResources\Clocking\UI\EditClocking;
use App\Actions\HumanResources\Clocking\UI\IndexClockings;
use App\Actions\HumanResources\Clocking\UI\RemoveClocking;
use App\Actions\HumanResources\Clocking\UI\ShowClocking;
use App\Actions\HumanResources\ClockingMachine\ExportWorkplaces;
use App\Actions\HumanResources\ClockingMachine\UI\CreateClockingMachine;
use App\Actions\HumanResources\ClockingMachine\UI\EditClockingMachine;
use App\Actions\HumanResources\ClockingMachine\UI\IndexClockingMachines;
use App\Actions\HumanResources\ClockingMachine\UI\RemoveClockingMachine;
use App\Actions\HumanResources\ClockingMachine\UI\ShowClockingMachine;
use App\Actions\HumanResources\Employee\CreateUserFromEmployee;
use App\Actions\HumanResources\Employee\ExportEmployees;
use App\Actions\HumanResources\Employee\ExportEmployeeTimesheets;
use App\Actions\HumanResources\Employee\UI\CreateEmployee;
use App\Actions\HumanResources\Employee\UI\EditEmployee;
use App\Actions\HumanResources\Employee\UI\IndexEmployees;
use App\Actions\HumanResources\Employee\UI\RemoveEmployee;
use App\Actions\HumanResources\Employee\UI\ShowEmployee;
use App\Actions\HumanResources\JobPosition\UI\CreateJobPosition;
use App\Actions\HumanResources\JobPosition\UI\EditJobPosition;
use App\Actions\HumanResources\JobPosition\UI\IndexJobPositions;
use App\Actions\HumanResources\JobPosition\UI\RemoveJobPosition;
use App\Actions\HumanResources\JobPosition\UI\ShowJobPosition;
use App\Actions\HumanResources\Timesheet\UI\IndexTimesheets;
use App\Actions\HumanResources\Timesheet\UI\ShowTimeSheet;
use App\Actions\HumanResources\Workplace\UI\CreateWorkplace;
use App\Actions\HumanResources\Workplace\UI\EditWorkplace;
use App\Actions\HumanResources\Workplace\UI\IndexWorkplaces;
use App\Actions\HumanResources\Workplace\UI\RemoveWorkplace;
use App\Actions\HumanResources\Workplace\UI\ShowWorkplace;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', [
    'uses'  => ShowHumanResourcesDashboard::class,
    'icon'  => 'user-hard-hat',
    'label' => 'human resources'

])->name('dashboard');
Route::get('/employees', IndexEmployees::class)->name('employees.index');
Route::get('/employees/create', CreateEmployee::class)->name('employees.create');

Route::get('/employees/export', ExportEmployees::class)->name('employees.export');

Route::get('/employees/{employee}', ShowEmployee::class)->name('employees.show');
Route::get('/employees/{employee}/edit', EditEmployee::class)->name('employees.edit');
Route::get('/employees/{employee}/delete', RemoveEmployee::class)->name('employees.remove');

Route::post('/employees/{employee}/user', ShowEmployee::class)->name('employees.show.user');
Route::post('/employees/{employee}/user', CreateUserFromEmployee::class)->name('employees.show.user.store');
Route::get('/employees/{employee}/timesheets/export', ExportEmployeeTimesheets::class)->name('employees.timesheets.export');

Route::get('/employees/{employee}/timesheets/{timesheet}', [ShowTimeSheet::class, 'inEmployee'])->name('employees.show.timesheets.show');

Route::get('/positions', IndexJobPositions::class)->name('job-positions.index');
Route::get('/positions/create', CreateJobPosition::class)->name('job-positions.create');
Route::get('/positions/{jobPosition}', ShowJobPosition::class)->name('job-positions.show');
Route::get('/positions/{jobPosition}/edit', EditJobPosition::class)->name('job-positions.edit');
Route::get('/positions/{jobPosition}/delete', RemoveJobPosition::class)->name('job-positions.remove');


Route::get('/calendars', IndexCalendars::class)->name('calendars.index');
Route::get('/calendars/{calendar}', ShowCalendar::class)->name('calendars.show');

Route::get('/time-sheets', IndexTimeSheets::class)->name('time-sheets.index');
Route::get('/time-sheets/{timeSheet}', ShowTimesheet::class)->name('time-sheets.show');


Route::get('/workplaces', IndexWorkplaces::class)->name('workplaces.index');
Route::get('/workplaces/create', CreateWorkplace::class)->name('workplaces.create');
Route::get('/workplaces/export', ExportWorkplaces::class)->name('workplaces.export');
Route::get('/workplaces/{workplace}', ShowWorkplace::class)->name('workplaces.show');
Route::get('/workplaces/{workplace}/edit', EditWorkplace::class)->name('workplaces.edit');
Route::get('/workplaces/{workplace}/delete', RemoveWorkplace::class)->name('workplaces.remove');

Route::scopeBindings()->group(function () {
    Route::get('/clocking-machines/{clockingMachine}/clockings', [IndexClockings::class, 'inClockingMachine'])->name('clocking-machines.clockings.index');
    Route::get('/clocking-machines/{clockingMachine}/clockings/create', [CreateClocking::class, 'inClockingMachine'])->name('clocking-machines.show.clockings.create');
    Route::get('/clocking-machines/{clockingMachine}/clockings/{clocking}', [ShowClocking::class, 'inClockingMachine'])->name('clocking-machines.show.clockings.show');
    Route::get('/clocking-machines/{clockingMachine}/clockings/{clocking}/edit', [EditClocking::class, 'inClockingMachine'])->name('clocking-machines.show.clockings.edit');
    Route::get('/clocking-machines/{clockingMachine}/clockings/{clocking}/delete', [RemoveClocking::class, 'inClockingMachine'])->name('clocking-machines.show.clockings.remove');

    Route::get('/workplaces/{workplace}/clocking-machines', [IndexClockingMachines::class, 'inWorkplace'])->name('workplaces.show.clocking-machines.index');
    Route::get('/workplaces/{workplace}/clocking-machines/create', CreateClockingMachine::class)->name('workplaces.show.clocking-machines.create');
    Route::get('/workplaces/{workplace}/clocking-machines/{clockingMachine}', [ShowClockingMachine::class, 'inWorkplace'])->name('workplaces.show.clocking-machines.show');
    Route::get('/workplaces/{workplace}/clocking-machines/{clockingMachine}/edit', [EditClockingMachine::class, 'inWorkplace'])->name('workplaces.show.clocking-machines.edit');
    Route::get('/workplaces/{workplace}/clocking-machines/{clockingMachine}/delete', [RemoveClockingMachine::class, 'inWorkplace'])->name('workplaces.show.clocking-machines.remove');

    Route::get('/workplaces/{workplace}/clocking-machines/{clockingMachine}/clockings', [IndexClockings::class, 'inWorkplaceInClockingMachine'])->name('workplaces.show.clocking-machines.show.clockings.index');
    Route::get('/workplaces/{workplace}/clocking-machines/{clockingMachine}/clockings/create', [CreateClocking::class, 'inWorkplaceInClockingMachine'])->name('workplaces.show.clocking-machines.show.clockings.create');
    Route::get('/workplaces/{workplace}/clocking-machines/{clockingMachine}/clockings/{clocking}/edit', [EditClocking::class, 'inWorkplaceInClockingMachine'])->name('workplaces.show.clocking-machines.show.clockings.edit');
    Route::get('/workplaces/{workplace}/clocking-machines/{clockingMachine}/clockings/{clocking}', [ShowClocking::class, 'inWorkplaceInClockingMachine'])->name('workplaces.show.clocking-machines.show.clockings.show');
    Route::get('/workplaces/{workplace}/clocking-machines/{clockingMachine}/clockings/{clocking}/delete', [RemoveClocking::class, 'inWorkplaceInClockingMachine'])->name('workplaces.show.clocking-machines.show.clockings.remove');

    Route::get('/workplaces/{workplace}/clockings', [IndexClockings::class, 'inWorkplace'])->name('workplaces.show.clockings.index');
    Route::get('/workplaces/{workplace}/clockings/create', [CreateClocking::class, 'inWorkplace'])->name('workplaces.show.clockings.create');
    Route::get('/workplaces/{workplace}/clockings/{clocking}', [ShowClocking::class, 'inWorkplace'])->name('workplaces.show.clockings.show');
    Route::get('/workplaces/{workplace}/clockings/{clocking}/edit', [EditClocking::class, 'inWorkplace'])->name('workplaces.show.clockings.edit');
    Route::get('/workplaces/{workplace}/clockings/{clocking}/delete', [RemoveClocking::class, 'inWorkplace'])->name('workplaces.show.clockings.remove');
});

Route::get('/clocking-machines', [IndexClockingMachines::class, 'inOrganisation'])->name('clocking-machines.index');
Route::get('/clocking-machines/create', CreateClockingMachine::class)->name('clocking-machines.create');
Route::get('/clocking-machines/{clockingMachine}', ShowClockingMachine::class)->name('clocking-machines.show');
Route::get('/clocking-machines/{clockingMachine}/edit', EditClockingMachine::class)->name('clocking-machines.edit');
Route::get('/clocking-machines/{clockingMachine}/delete', RemoveClockingMachine::class)->name('clocking-machines.remove');

Route::get('/clocking', IndexClockings::class)->name('clockings.index');
Route::get('/clocking/create', CreateClocking::class)->name('clockings.create');
Route::get('/clocking/{clocking}', ShowClocking::class)->name('clockings.show');
Route::get('/clocking/{clocking}/edit', EditClocking::class)->name('clockings.edit');
Route::get('/clocking/{clocking}/delete', RemoveClocking::class)->name('clockings.remove');
