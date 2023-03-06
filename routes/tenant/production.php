<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 08:28:40 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Production\ShowProductionDashboard;

Route::get('/', ShowProductionDashboard::class)->name('dashboard');
Route::get('/products/', ShowProductionDashboard::class)->name('products.index');
