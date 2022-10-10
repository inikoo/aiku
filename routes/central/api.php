<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 18 Aug 2022 14:07:24 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

use App\Actions\Central\Deployment\ShowDeployment;
use App\Actions\Central\Deployment\StoreDeployment;
use App\Actions\Central\Deployment\UpdateDeployment;
use Illuminate\Support\Facades\Route;





Route::get('/deployments/latest', [ShowDeployment::class, 'latest'])->name('deployments.latest');
Route::get('/deployments/{deployment}', ShowDeployment::class)->name('deployments.show');
Route::post('/deployments/create', StoreDeployment::class)->name('deployments.store');
Route::post('/deployments/latest/edit', [UpdateDeployment::class, 'latest'])->name('deployments.edit.latest');

