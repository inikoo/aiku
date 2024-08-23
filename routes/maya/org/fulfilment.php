<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Jan 2024 15:20:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentRentals;

Route::prefix('rentals')->as('rentals.')->group(function () {
    Route::get('/', [IndexFulfilmentRentals::class, 'maya'])->name('index');
});