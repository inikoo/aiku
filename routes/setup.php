<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 12 Aug 2022 00:20:56 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

use App\Actions\Organisations\Setup\SetupAccessCode;
use App\Actions\Organisations\Setup\SetupUsername;
use App\Actions\Organisations\Setup\ShowSetup;

Route::get('/', ShowSetup::class)->name('root');
Route::post('/username', SetupUsername::class)->name('username');
Route::post('/access-code',SetupAccessCode::class)->name('access-code');

