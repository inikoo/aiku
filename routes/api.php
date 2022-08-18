<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 18 Aug 2022 14:07:24 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

use App\Http\Controllers\Admin\DeploymentController;
use Illuminate\Support\Facades\Route;




Route::get('/deployments/latest', [DeploymentController::class, 'latest'])->name('deployments.latest');
Route::get('/deployments/{deploymentId}', [DeploymentController::class, 'show'])->name('deployments.show');
Route::post('/deployments/create', [DeploymentController::class, 'store'])->name('deployments.store');
Route::post('/deployments/latest/edit', [DeploymentController::class, 'updateLatest'])->name('deployments.edit.latest');

