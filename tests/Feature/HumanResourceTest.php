<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 26 Apr 2023 15:26:32 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\Tenancy\Group\StoreGroup;
use App\Actions\Tenancy\Tenant\StoreTenant;
use App\Models\Tenancy\Group;
use App\Models\Tenancy\Tenant;
use App\Models\HumanResources\Employee;
use App\Actions\HumanResources\Employee\UpdateEmployee;
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

test('create employees successful', function () {
    $arrayData = [
        'contact_name' => 'artha',
        'date_of_birth'=> '2019-01-01',
        'job_title'    => 'director',
        'state'        => 'hired'
    ];

    StoreEmployee::run($arrayData);

    //    $this->assertDatabaseHas('employees',$arrayData);
    $lastEmployee = Employee::latest()->first();
    expect($lastEmployee->contact_name)->toBe($arrayData['contact_name']);
});

test('update employees successful', function () {
    $employee  = Employee::latest()->first();
    $arrayData = [
        'contact_name' => 'vica',
        'date_of_birth'=> '2019-01-01',
        'job_title'    => 'director',
        'state'        => 'hired'
    ];

    UpdateEmployee::run($employee, $arrayData);

    //    $this->assertDatabaseHas('employees',$lastEmployee);
    $lastEmployee = Employee::latest()->first();
    expect($lastEmployee->contact_name)->toBe($arrayData['contact_name']);
});

test('update employee working hours', function () {
    $employee = Employee::latest()->first();

    $employee = UpdateEmployeeWorkingHours::run($employee, [10]);

    //    $this->assertDatabaseHas('employees',$employee);
    expect($employee['working_hours'])->toBeArray(10);
});

test('create user from employee', function () {
    $arrayData = Employee::latest()->first();

    $lastEmployee = CreateUserFromEmployee::run($arrayData);

    //    $this->assertDatabaseHas('employees',$lastEmployee);
    expect($lastEmployee->contact_name)->toBe($arrayData->contact_name);
});
