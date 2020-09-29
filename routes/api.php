<?php

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
