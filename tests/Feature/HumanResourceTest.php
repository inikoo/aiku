<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 26 Apr 2023 15:26:32 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\HumanResources\ClockingMachine\StoreClockingMachine;
use App\Actions\HumanResources\ClockingMachine\UpdateClockingMachine;
use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployee;
use App\Actions\HumanResources\WorkingPlace\UpdateWorkingPlace;
use App\Actions\Organisation\Group\StoreGroup;
use App\Actions\Organisation\Organisation\StoreOrganisation;
use App\Models\Auth\User;
use App\Models\Helpers\Address;
use App\Models\HumanResources\Workplace;
use App\Models\Organisation\Group;
use App\Models\Organisation\Organisation;
use App\Models\HumanResources\Employee;
use App\Actions\HumanResources\Employee\UpdateEmployeeWorkingHours;
use App\Actions\HumanResources\Employee\CreateUserFromEmployee;

beforeAll(function () {
    loadDB('test_base_database.dump');
});

beforeEach(function () {
    $organisation = Organisation::first();
    if (!$organisation) {
        $group        = StoreGroup::make()->action(Group::factory()->definition());
        $organisation = StoreOrganisation::make()->action($group, Organisation::factory()->definition());
    }
    $organisation->makeCurrent();
});

test('create employee successful', function () {
    $arrayData = [
        'contact_name' => 'artha',
        'date_of_birth'=> '2019-01-01',
        'job_title'    => 'director',
        'state'        => 'hired'
    ];

    $lastEmployee = StoreEmployee::run($arrayData);

    expect($lastEmployee->contact_name)->toBe($arrayData['contact_name']);

    return $lastEmployee;
});

test('update employees successful', function ($lastEmployee) {
    $arrayData = [
        'contact_name' => 'vica',
        'date_of_birth'=> '2019-01-01',
        'job_title'    => 'director',
        'state'        => 'hired'
    ];

    $updatedEmployee = UpdateEmployee::run($lastEmployee, $arrayData);

    expect($updatedEmployee->contact_name)->toBe($arrayData['contact_name']);
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

test('create working place successful', function () {
    $arrayData = [
        'name'  => 'artha',
        'type'  => 'branch'
    ];


    $createdWorkplace = Workplace::create($arrayData);

    expect($createdWorkplace->name)->toBe($arrayData['name']);

    return $createdWorkplace;
});

test('update working place successful', function ($createdWorkplace) {
    $arrayData = [
        'name'              => 'vica nugraha',
        'type'              => 'home',
    ];
    $addressData      = Address::create(Address::factory()->definition())->toArray();
    $updatedWorkplace = UpdateWorkingPlace::run($createdWorkplace, $arrayData, $addressData);

    expect($updatedWorkplace->name)->toBe($arrayData['name']);
})->depends('create working place successful');

test('create clocking machines', function ($createdWorkplace) {
    $arrayData =[
        'code' => 'ABC'
    ];

    $clockingMachine = StoreClockingMachine::run($createdWorkplace, $arrayData);
    expect($clockingMachine->code)->toBe($arrayData['code']);

    return $clockingMachine;
})->depends('create working place successful');


test('update clocking machines', function ($createdClockingMachine) {
    $arrayData = [
        'code'              => 'ABCED',
    ];

    $updatedClockingMachine = UpdateClockingMachine::run($createdClockingMachine, $arrayData);

    expect($updatedClockingMachine->code)->toBe($arrayData['code']);
})->depends('create clocking machines');
