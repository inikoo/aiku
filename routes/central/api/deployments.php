<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 17 Nov 2022 11:58:09 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Actions\DevOps\Deployment\ShowDeployment;
use App\Actions\DevOps\Deployment\StoreDeployment;
use App\Actions\DevOps\Deployment\UpdateDeployment;
use Illuminate\Support\Facades\Route;

Route::get('/latest', [ShowDeployment::class, 'latest'])->name('latest');
Route::get('/{deployment}', ShowDeployment::class)->name('show');
Route::post('/create', StoreDeployment::class)->name('store');
Route::patch('/latest/edit', [UpdateDeployment::class, 'latest'])->name('latest.edit');
