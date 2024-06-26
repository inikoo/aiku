<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 13 Jun 2024 13:27:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\HumanResources\ClockingMachine\StoreClockingMachine;
use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Workplace\StoreWorkplace;
use App\Enums\HumanResources\ClockingMachine\ClockingMachineTypeEnum;
use App\Enums\HumanResources\Workplace\WorkplaceTypeEnum;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\Workplace;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {

    $this->organisation = createOrganisation();
    $this->adminGuest   = createAdminGuest($this->organisation->group);

    $workplace = Workplace::first();
    if (!$workplace) {
        data_set($storeData, 'name', 'workplace');
        data_set($storeData, 'type', WorkplaceTypeEnum::HQ->value);

        $workplace = StoreWorkplace::make()->action(
            $this->organisation,
            $storeData
        );
    }
    $this->workplace = $workplace;

    $clockingMachine = ClockingMachine::first();
    if (!$clockingMachine) {
        data_set($storeData, 'name', 'machine');
        data_set($storeData, 'type', ClockingMachineTypeEnum::BIOMETRIC->value);

        $clockingMachine = StoreClockingMachine::make()->action(
            $this->workplace,
            $storeData
        );
    }
    $this->clockingMachine = $clockingMachine;

    $employee = Employee::first();
    if (!$employee) {
        $storeData = Employee::factory()->definition();

        $employee = StoreEmployee::make()->action(
            $this->organisation,
            $storeData
        );
    }
    $this->employee = $employee;


    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->adminGuest->user);
});

test('UI Index calendar', function () {
    $response = $this->get(route('grp.org.hr.calendars.index', [$this->organisation->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/HumanResources/Calendar')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'employees')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI show calendar', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.hr.calendars.show', [$this->organisation->slug, $this->employee->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/HumanResources/Calendar')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->employee->worker_number)
                        ->etc()
            )
            ->has('tabs');

    });
})->todo(); //authorization problem

test('UI Index clockings', function () {
    $this->withoutExceptionHandling();
    $response = $this->get(route('grp.org.hr.workplaces.show.clockings.index', [$this->organisation->slug, $this->workplace->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/HumanResources/Clockings')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'clockings')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI Index clocking machines', function () {
    $this->withoutExceptionHandling();
    $response = $this->get(route('grp.org.hr.workplaces.show.clocking_machines.index', [$this->organisation->slug, $this->workplace->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/HumanResources/ClockingMachines')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Clocking machines')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI show clocking machine', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.hr.workplaces.show.clocking_machines.show', [$this->organisation->slug, $this->workplace->slug, $this->clockingMachine->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/HumanResources/ClockingMachine')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->clockingMachine->name)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI create clocking machine', function () {
    $response = get(route('grp.org.hr.workplaces.show.clocking_machines.create', [$this->organisation->slug, $this->workplace->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')->has('formData')->has('pageHead')->has('breadcrumbs', 5);
    });
});

test('UI edit clocking machine', function () {
    $response = get(route('grp.org.hr.workplaces.show.clocking_machines.edit', [$this->organisation->slug, $this->workplace->slug, $this->clockingMachine->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('formData.blueprint.0.fields', 1)
            ->has('pageHead')
            ->has(
                'formData.args.updateRoute',
                fn (AssertableInertia $page) => $page
                        ->where('name', 'grp.models.clocking_machine..update')
                        ->where('parameters', $this->clockingMachine->id) 
            )
            ->has('breadcrumbs', 4);
    });
});

test('UI Index employees', function () {
    $response = $this->get(route('grp.org.hr.employees.index', [$this->organisation->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/HumanResources/Employees')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'employees')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI create employee', function () {
    $response = get(route('grp.org.hr.employees.create', [$this->organisation->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')->has('formData')->has('pageHead')->has('breadcrumbs', 4);
    });
});

test('UI show employee', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.hr.employees.show', [$this->organisation->slug, $this->employee->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/HumanResources/Employee')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->employee->contact_name)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI edit employee', function () {
    $response = get(route('grp.org.hr.employees.edit', [$this->organisation->slug, $this->employee->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('pageHead')
            ->has('formData')
            ->has(
                'formData.args.updateRoute',
                fn (AssertableInertia $page) => $page
                        ->where('name', 'grp.models.employee.update')
                        ->where('parameters', [$this->employee->id]) 
            )
            ->has('breadcrumbs', 3);
    });
});