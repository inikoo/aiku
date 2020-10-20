<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 20 Oct 2020 15:37:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Legacy\EmployeeController;
use App\Http\Controllers\Legacy\GuestController;


Route::post(
    '/employee', [
                   EmployeeController::class,
                   'sync'
               ]
)->name('sync_employee');

Route::post(
    '/employee/{legacy_id}', [
                               EmployeeController::class,
                               'update'
                           ]
)->name('update_employer');

Route::post(
    '/guest', [
                GuestController::class,
                'sync'
            ]
)->name('sync_guest');

Route::post(
    '/guest/{legacy_id}', [
                            GuestController::class,
                            'update'
                        ]
)->name('update_guest');
