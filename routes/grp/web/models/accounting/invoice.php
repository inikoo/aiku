<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 13 Sept 2024 13:14:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Accounting\Invoice\UpdateInvoice;

use App\Stubs\UIDummies\ShowDummy;
use Illuminate\Support\Facades\Route;

Route::name('invoice.')->prefix('invoice/{invoice:id}')->group(function () {
    Route::patch('update', UpdateInvoice::class)->name('update');
    Route::post('payment', ShowDummy::class)->name('payment.store');



});
