<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 16:07:09 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Sales\Customer\IndexCustomers;
use App\Actions\Sales\Customer\ShowCustomer;


Route::get('/', IndexCustomers::class)->name('index');
Route::get('/{customer}', ShowCustomer::class)->name('show');
