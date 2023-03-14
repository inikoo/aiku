<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 10:25:11 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */


use App\Actions\HumanResources\Employee\UpdateEmployee;
use App\Actions\Marketing\Department\UpdateDepartment;
use App\Actions\Sales\Customer\UpdateCustomer;
use Illuminate\Support\Facades\Route;

Route::patch('/customer/{customer}', UpdateCustomer::class)->name('customer.update');

Route::patch('/department/{department}', UpdateDepartment::class)->name('department.update');
Route::patch('/employee/{employee}', UpdateEmployee::class)->name('employee.update');
