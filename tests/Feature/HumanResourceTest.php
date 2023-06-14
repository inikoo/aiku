<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 26 Apr 2023 15:26:32 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployee;
use App\Actions\HumanResources\WorkingPlace\UpdateWorkingPlace;
use App\Actions\Tenancy\Group\StoreGroup;
use App\Actions\Tenancy\Tenant\StoreTenant;
use App\Models\Helpers\Address;
use App\Models\HumanResources\Workplace;
use App\Models\Tenancy\Group;
use App\Models\Tenancy\Tenant;
use App\Models\HumanResources\Employee;
use App\Actions\HumanResources\Employee\UpdateEmployeeWorkingHours;
use App\Actions\HumanResources\Employee\CreateUserFromEmployee;

beforeAll(function () {
    loadDB('test_base_database.dump');
});

beforeEach(function () {
    $tenant = Tenant::first();
    if (!$tenant) {
        $group  = StoreGroup::make()->asAction(Group::factory()->definition());
        $tenant = StoreTenant::make()->action($group, Tenant::factory()->definition());
    }
    $tenant->makeCurrent();
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

    $createdEmployee = CreateUserFromEmployee::run($lastEmployee);

    expect($createdEmployee->contact_name)->toBe($lastEmployee->contact_name);
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
