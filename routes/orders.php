<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Feb 2023 22:57:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Sales\Order\IndexOrders;



Route::get('/', IndexOrders::class)->name('index');
//Route::get('/{order}', ShowOrder::class)->name('show');
