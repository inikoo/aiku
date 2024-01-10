<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Jan 2024 03:24:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Dispatch\Picking\ExportPicking;
use App\Actions\UI\Dispatch\ShowDispatchHub;

if (empty($parent)) {
    $parent = 'tenant';
}

Route::get('/pickings/export', ExportPicking::class)->name('picking.export');
Route::get('/', [ShowDispatchHub::class, $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('hub');
