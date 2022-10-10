<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 10 Oct 2022 10:38:10 Central European Summer Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Actions\SourceFetch\Aurora\FetchCustomers;
use Illuminate\Support\Facades\Route;


Route::post('/{tenant:uuid}/customer', FetchCustomers::class)->name('aurora.customers');
