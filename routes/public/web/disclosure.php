<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 12 Sep 2023 18:51:38 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\UI\Public\Disclosure\ShowClosedDown;
use App\Actions\UI\Public\Disclosure\ShowUnderConstruction;
use App\Actions\UI\Public\Disclosure\ShowUnderMaintenance;
use Illuminate\Support\Facades\Route;

Route::get('/under-construction', ShowUnderConstruction::class)->name('under-construction');
Route::get('/under-maintenance', ShowUnderMaintenance::class)->name('under-maintenance');
Route::get('/closed-down', ShowClosedDown::class)->name('closed-down');
