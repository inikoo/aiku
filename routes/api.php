<?php

use App\Http\Controllers\Admin\DeploymentController;
use Illuminate\Support\Facades\Route;




Route::get('/deployments/latest', [DeploymentController::class, 'latest'])->name('deployments.latest');
Route::get('/deployments/{deploymentId}', [DeploymentController::class, 'show'])->name('deployments.show');
Route::post('/deployments/create', [DeploymentController::class, 'store'])->name('deployments.store');
Route::post('/deployments/latest/edit', [DeploymentController::class, 'updateLatest'])->name('deployments.edit.latest');

