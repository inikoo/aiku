<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 08:28:40 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Sales\Order\IndexOrders;
use App\Actions\Sales\Order\ShowOrder;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexOrders::class)->name('index');
Route::get('/{order}', ShowOrder::class)->name('show');
