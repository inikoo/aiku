<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:44:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Dispatch\Picking\ExportPicking;
use App\Actions\UI\Dispatch\ShowDispatchHub;

if (empty($parent)) {
    $parent = 'tenant';
}

Route::get('/pickings/export', ExportPicking::class)->name('picking.export');
Route::get('/', [ShowDispatchHub::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('hub');
