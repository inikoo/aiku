<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 12:33:52 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Dispatch\Picking\ExportPicking;
use App\Actions\UI\Dispatch\ShowDispatchHub;

//Route::get('/pickings/export', ExportPicking::class)->name('picking.export');
Route::get('/', ShowDispatchHub::class)->name('backlog');
