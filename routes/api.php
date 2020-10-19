<?php

use App\Http\Controllers\CustomerClientController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', 'ApiLoginController@authenticate');
Route::post('/logout', 'ApiLogoutController@logout');
Route::post('/token', 'ApiTokenController@authenticate');


Route::post('/register-app', 'ApiTokenController@registerApp');


Route::post('/app/access-code', 'AppAuthController@createAccessCode');
Route::post('/app/login/access-code', 'AppAuthController@loginWithAccessCode');


Route::middleware('auth:sanctum')->get(
    '/user', function (Request $request) {
    return $request->user();
}
);


Route::middleware('auth:sanctum')->prefix('legacy')->group(
    function () {
        Route::post(
            'employee', [
                          EmployeeController::class,
                          'update'
                      ]
        )->name('update_employee');
        Route::post(
            'user', [
                      UserController::class,
                      'update'
                  ]
        )->name('update_user');

        Route::post(
            'store', [
                       StoreController::class,
                       'update'
                   ]
        )->name('update_store');


        Route::post(
            'customer/{legacy_id}', [
                                      CustomerController::class,
                                      'update'
                                  ]
        )->name('update_customer');
        Route::post(
            'customer', [
                          CustomerController::class,
                          'create'
                      ]
        )->name('create_customer');

        Route::post(
            'customer_client/{legacy_id}', [
                                 CustomerClientController::class,
                                 'update'
                             ]
        )->name('update_customer_client');
        Route::post(
            'customer_client', [
                                 CustomerClientController::class,
                                 'create'
                             ]
        )->name('update_customer_client');


        Route::post(
            'stock', [
                       StockController::class,
                       'update'
                   ]
        )->name('update_stock');

    }
);
