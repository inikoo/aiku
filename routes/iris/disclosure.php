<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Feb 2024 16:36:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\UI\Iris\Disclosure\ShowClosedDown;
use App\Actions\UI\Iris\Disclosure\ShowUnderConstruction;
use App\Actions\UI\Iris\Disclosure\ShowUnderMaintenance;
use Illuminate\Support\Facades\Route;

Route::get('/under-construction', ShowUnderConstruction::class)->name('under-construction');
Route::get('/under-maintenance', ShowUnderMaintenance::class)->name('under-maintenance');
Route::get('/closed-down', ShowClosedDown::class)->name('closed-down');
