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
        );
        Route::post(
            'user', [
                      UserController::class,
                      'update'
                  ]
        );

        Route::post(
            'store', [
                       StoreController::class,
                       'update'
                   ]
        );
        Route::post(
            'customer', [
                          CustomerController::class,
                          'update'
                      ]
        );
        Route::post(
            'customer_client', [
                                 CustomerClientController::class,
                                 'update'
                             ]
        );

        Route::post(
            'stock', [
                       StockController::class,
                       'update'
                   ]
        );

    }
);
