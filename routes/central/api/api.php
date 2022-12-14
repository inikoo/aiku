<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 17 Nov 2022 11:58:09 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */



Route::prefix('deployments')
    ->name('deployments.')
    ->group(__DIR__.'/deployments.php');

Route::prefix('aurora')
    ->name('aurora.')
    ->group(__DIR__.'/aurora.php');

Route::prefix('iris')
    ->name('iris.')
    ->group(__DIR__.'/iris/iris.php');
