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

test('create employees', function () {
    $employee = StoreEmployee::run(Employee::factory()->definition());

    $this->assertModelExists($employee);
});

test('update employees', function () {
    $employee = Employee::latest()->first();
    $employee = UpdateEmployee::run($employee, Employee::factory()->definition());

    $this->assertModelExists($employee);
});

test('update employee working hours', function () {
    $employee = Employee::latest()->first();
    $employee = UpdateEmployeeWorkingHours::run($employee, [10]);

    $this->assertModelExists($employee);
});

test('create user from employee', function () {
    $employee = Employee::latest()->first();
    $employee = CreateUserFromEmployee::run($employee);

    $this->assertModelExists($employee);
});
