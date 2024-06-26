<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 13 Jun 2024 13:27:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Workplace\StoreWorkplace;
use App\Enums\HumanResources\Workplace\WorkplaceTypeEnum;
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