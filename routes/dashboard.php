<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 20 Jan 2023 14:20 Malaga Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Dashboard\DisplayDashTV;
use App\Actions\SysAdmin\ShowSysAdminDashboard;
use Illuminate\Support\Facades\Route;


Route::get('/tv', DisplayDashTV::class)->name('tv');
Route::get('/', ShowSysAdminDashboard::class)->name('show');

