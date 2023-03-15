<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 10:25:11 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */


use App\Actions\HumanResources\Employee\UpdateEmployee;
use App\Actions\Inventory\Location\UpdateLocation;
use App\Actions\Inventory\Warehouse\UpdateWarehouse;
use App\Actions\Inventory\WarehouseArea\UpdateWarehouseArea;
use App\Actions\Marketing\Department\UpdateDepartment;
use App\Actions\Marketing\Family\UpdateFamily;
use App\Actions\Marketing\Product\UpdateProduct;
use App\Actions\Sales\Customer\UpdateCustomer;
use Illuminate\Support\Facades\Route;

Route::patch('/customer/{customer}', UpdateCustomer::class)->name('customer.update');

Route::patch('/product/{product}', UpdateProduct::class)->name('product.update');

Route::patch('/family/{family}', UpdateFamily::class)->name('family.update');

Route::patch('/department/{department}', UpdateDepartment::class)->name('department.update');

Route::patch('/employee/{employee}', UpdateEmployee::class)->name('employee.update');

Route::patch('/warehouse/{warehouse}', UpdateWarehouse::class)->name('warehouse.update');

Route::patch('/areas/{warehouseArea}', UpdateWarehouseArea::class)->name('warehouse_area.update');

Route::patch('/location/{location}', UpdateLocation::class)->name('location.update');
