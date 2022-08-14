<?php

use App\Http\Controllers\Admin\DeploymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::get('/deployments/latest', [DeploymentController::class, 'latest'])->name('deployments.latest');
Route::get('/deployments/{deploymentId}', [DeploymentController::class, 'show'])->name('deployments.show');
Route::post('/deployments/create', [DeploymentController::class, 'store'])->name('deployments.store');
Route::post('/deployments/latest/edit', [DeploymentController::class, 'updateLatest'])->name('deployments.edit.latest');

