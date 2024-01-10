<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:47:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\UI\Production\ProductionDashboard;

Route::get('/', ProductionDashboard::class)->name('dashboard');
Route::get('/products/', ProductionDashboard::class)->name('products.index');
