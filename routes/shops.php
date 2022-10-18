<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 16:27:58 Central European Summer Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Actions\Marketing\SHop\IndexShops;
use App\Actions\Marketing\Shop\ShowShop;
use App\Actions\Sales\Customer\IndexCustomers;
use App\Actions\Sales\Customer\ShowCustomer;

Route::get('/', IndexShops::class)->name('index');
Route::get('/{shop}', ShowShop::class)->name('show');

Route::get('/{shop}/customers', [IndexCustomers::class, 'inShop'])->name('show.customers.index');
Route::get('/{shop}/customers/{customer}', [ShowCustomer::class, 'InShop'])->name('show.customers.show');


