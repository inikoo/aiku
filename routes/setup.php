<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 12 Aug 2022 00:20:56 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

use App\Http\Controllers\Setup\SetupController;

Route::get('/', [SetupController::class, 'root'])->name('root');
Route::post('/username', [SetupController::class, 'setupUsername'])->name('username');


