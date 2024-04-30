<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 26 Apr 2023 15:26:32 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\HumanResources\Clocking\StoreClocking;
use App\Actions\HumanResources\ClockingMachine\StoreClockingMachine;
use App\Actions\HumanResources\ClockingMachine\UpdateClockingMachine;
use App\Actions\HumanResources\Employee\CreateUserFromEmployee;
use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployeeWorkingHours;
use App\Actions\HumanResources\Timesheet\StoreTimesheet;
use App\Actions\HumanResources\Workplace\StoreWorkplace;
use App\Actions\HumanResources\Workplace\UpdateWorkplace;
use App\Enums\HumanResources\Clocking\ClockingTypeEnum;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Enums\HumanResources\TimeTracker\TimeTrackerStatusEnum;
use App\Enums\HumanResources\Workplace\WorkplaceTypeEnum;
use App\Models\Helpers\Address;
use App\Models\HumanResources\Clocking;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\Timesheet;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\User;

use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB('test_base_database.dump');
});

beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->adminGuest   = createAdminGuest($this->organisation->group);

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->adminGuest->user);
});

test('check seeded job positions', function () {
    expect($this->organisation->group->humanResourcesStats->number_job_positions)->toBe(19);
});

test('create working place successful', function () {
    $modelData = [
        'name'    => 'office',
        'type'    => WorkplaceTypeEnum::BRANCH,
        'address' => Address::factory()->definition()
    ];

    $workplace = StoreWorkplace::make()->action($this->organisation, $modelData);
    expect($workplace)->toBeInstanceOf(Workplace::class)
        ->and($this->organisation->humanResourcesStats->number_workplaces)->toBe(1)
        ->and($this->organisation->humanResourcesStats->number_workplaces_type_branch)->toBe(1);


    return $workplace;
});

test('update working place successful', function ($createdWorkplace) {
    $arrayData = [
        'name'    => 'vica smith',
        'type'    => WorkplaceTypeEnum::HOME,
        'address' => Address::factory()->definition()
    ];

    $updatedWorkplace = UpdateWorkplace::run($createdWorkplace, $arrayData);

    expect($updatedWorkplace->name)->toBe($arrayData['name'])
        ->and($this->organisation->humanResourcesStats->number_workplaces_type_branch)->toBe(0)
        ->and($this->organisation->humanResourcesStats->number_workplaces_type_home)->toBe(1);
})->depends('create working place successful');

test('create working place by command', function () {
    $this->artisan("workplace:create {$this->organisation->slug} office2 hq")->assertExitCode(0);
    $this->artisan("workplace:create {$this->organisation->slug} office2 hq")->assertExitCode(1);
    $workplace = Workplace::where('name', 'office2')->first();
    $this->organisation->refresh();
    expect($workplace)->not->toBeNull()
        ->and($this->organisation->humanResourcesStats->number_workplaces)->toBe(2);
});

test('create employee successful', function () {
    $arrayData = [
        'alias'               => 'artha',
        'contact_name'        => 'artha',
        'employment_start_at' => '2019-01-01',
        'date_of_birth'       => '2000-01-01',
        'job_title'           => 'director',
        'state'               => EmployeeStateEnum::HIRED,
        'positions'           => ['acc-m'],
        'worker_number'       => '1234567890',
        'work_email'          => null,
        'email'               => null,
        'username'            => null,
    ];
    $employee  = StoreEmployee::make()->action($this->organisation, $arrayData);

    expect($employee)->toBeInstanceOf(Employee::class)
        ->and($this->organisation->humanResourcesStats->number_employees)->toBe(1)
        ->and($this->organisation->humanResourcesStats->number_employees_type_employee)->toBe(1)
        ->and($this->organisation->humanResourcesStats->number_employees_state_hired)->toBe(1)
        ->and($this->organisation->humanResourcesStats->number_employees_state_working)->toBe(0);

    return $employee;
});

test('update employees successful', function ($lastEmployee) {
    $arrayData = [
        'contact_name'  => 'vica',
        'date_of_birth' => '2019-01-01',
        'job_title'     => 'director',
        'state'         => EmployeeStateEnum::WORKING
    ];

    $updatedEmployee = UpdateEmployee::run($lastEmployee, $arrayData);

    expect($updatedEmployee->contact_name)->toBe($arrayData['contact_name'])
        ->and($this->organisation->humanResourcesStats->number_employees)->toBe(1)
        ->and($this->organisation->humanResourcesStats->number_employees_type_employee)->toBe(1)
        ->and($this->organisation->humanResourcesStats->number_employees_state_hired)->toBe(0)
        ->and($this->organisation->humanResourcesStats->number_employees_state_working)->toBe(1);
})->depends('create employee successful');

test('update employee working hours', function () {
    $lastEmployee = Employee::latest()->first();

    $updatedEmployee = UpdateEmployeeWorkingHours::run($lastEmployee, [10]);

    expect($updatedEmployee['working_hours'])->toBeArray(10);
});

test('create user from employee', function () {
    $lastEmployee = Employee::latest()->first();
    expect($lastEmployee)->toBeInstanceOf(Employee::class);
    $user = CreateUserFromEmployee::run($lastEmployee);
    expect($user)->toBeInstanceOf(User::class)
        ->and($user->contact_name)->toBe($lastEmployee->contact_name);
});


test('create clocking machines', function ($workplace) {
    $arrayData = [
        'name' => 'ABC',
        'type' => 'static-nfc',
    ];

    $clockingMachine = StoreClockingMachine::run($workplace, $arrayData);
    expect($clockingMachine->name)->toBe($arrayData['name']);

    return $clockingMachine;
})->depends('create working place successful');


test('update clocking machines', function ($createdClockingMachine) {
    $arrayData = [
        'name' => 'XYZ',
    ];

    $updatedClockingMachine = UpdateClockingMachine::run($createdClockingMachine, $arrayData);

    expect($updatedClockingMachine->name)->toBe($arrayData['name']);
})->depends('create clocking machines');

test('can show hr dashboard', function () {
    $response = get(route('grp.org.hr.dashboard', $this->organisation->slug));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('HumanResources/HumanResourcesDashboard')
            ->has('breadcrumbs', 2)
            ->where('stats.0.stat', 1)->where('stats.0.href.name', 'grp.org.hr.employees.index')
            ->where('stats.1.stat', 2)->where('stats.1.href.name', 'grp.org.hr.workplaces.index');
    });
});

test('can show list of workplaces', function () {
    $response = get(route('grp.org.hr.workplaces.index', $this->organisation->slug));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('HumanResources/Workplaces')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('data.data', 2);
    });
});

test('can show workplace', function () {
    $workplace = Workplace::first();
    $response  = get(route('grp.org.hr.workplaces.show', [$this->organisation->slug, $workplace->slug]));

    $response->assertInertia(function (AssertableInertia $page) use ($workplace) {
        $page
            ->component('HumanResources/Workplace')
            ->has('breadcrumbs', 3)
            ->where('pageHead.meta.0.href.name', 'grp.org.hr.workplaces.show.clocking-machines.index')
            ->where('pageHead.meta.0.href.parameters', [$this->organisation->slug, $workplace->slug])
            ->has('tabs.navigation', 5);
    });
});

test('can show list of employees', function () {
    $response = get(route('grp.org.hr.employees.index', $this->organisation->slug));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('HumanResources/Employees')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('data.data', 1);
    });
});

test('can show employee', function () {
    $employee = Employee::first();
    expect($employee->user)->toBeInstanceOf(User::class);

    $response = get(route('grp.org.hr.employees.show', [$this->organisation->slug, $employee->slug]));

    $response->assertInertia(function (AssertableInertia $page) use ($employee) {
        $page
            ->component('HumanResources/Employee')
            ->has('breadcrumbs', 3)
            ->where('pageHead.meta.1.href.name', 'grp.org.sysadmin.users.show')
            ->where('pageHead.meta.1.href.parameters', $employee->alias)
            ->has('tabs.navigation', 7);
    });
})->todo();

test('new timesheet for employee', function (Employee $employee) {
    $timesheet = StoreTimesheet::make()->action($employee, [
        'date' => now(),
    ]);

    expect($timesheet)->toBeInstanceOf(Timesheet::class)
        ->and($timesheet->subject_id)->toBe($employee->id)
        ->and($timesheet->subject_type)->toBe('Employee')
        ->and($timesheet->number_breaks)->toBe(0)
        ->and($timesheet->number_time_trackers)->toBe(0)
        ->and($timesheet->working_minutes)->toBe(0)
        ->and($timesheet->breaks_minutes)->toBe(0)
        ->and($employee->stats->number_timesheets)->toBe(1);

    return $timesheet;

})->depends('create employee successful');

test('create clocking', function (Timesheet $timesheet, Workplace $workplace) {
    /** @var Employee $employee */
    $employee = $timesheet->subject;

    $clocking=StoreClocking::make()->action($this->organisation, $workplace, $employee, [
        'clocked_at' => now()->subMinutes(10),
    ]);
    $clocking->refresh();

    expect($clocking)->toBeInstanceOf(Clocking::class)
        ->and($clocking->subject_id)->toBe($employee->id)
        ->and($clocking->subject_type)->toBe('Employee')
        ->and($clocking->workplace_id)->toBe($workplace->id)
        ->and($clocking->clocking_machine_id)->toBeNull()
        ->and($clocking->type)->toBe(ClockingTypeEnum::MANUAL)
        ->and($employee->stats->number_timesheets)->toBe(1)
        ->and($employee->stats->number_clockings)->toBe(1)
            ->and($employee->stats->number_time_trackers)->toBe(1);

    return $timesheet;

})->depends('new timesheet for employee', 'create working place successful');

test('second clocking ', function (Timesheet $timesheet, Workplace $workplace) {
    /** @var Employee $employee */
    $employee = $timesheet->subject;

    $clocking=StoreClocking::make()->action($this->organisation, $workplace, $employee, [
        'clocked_at' => now()->subMinutes(5),
    ]);
    $clocking->refresh();
    $timesheet=$clocking->timesheet;
    $employee->refresh();

    expect($clocking)->toBeInstanceOf(Clocking::class)
        ->and($clocking->subject_id)->toBe($employee->id)
        ->and($clocking->subject_type)->toBe('Employee')
        ->and($clocking->workplace_id)->toBe($workplace->id)
        ->and($clocking->clocking_machine_id)->toBeNull()
        ->and($timesheet->number_time_trackers)->toBe(1)
        ->and($clocking->type)->toBe(ClockingTypeEnum::MANUAL)
        ->and($employee->stats->number_timesheets)->toBe(1)
        ->and($employee->stats->number_clockings)->toBe(2)
        ->and($employee->stats->number_time_trackers)->toBe(1);

    $timeTracker=$timesheet->timeTrackers->first();
    expect($timeTracker->status)->toBe(TimeTrackerStatusEnum::CLOSED)
        ->and($timeTracker->end_clocking_id)->toBe($clocking->id);

})->depends('create clocking', 'create working place successful');
