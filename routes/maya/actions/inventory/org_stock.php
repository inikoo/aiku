<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 28 Aug 2024 22:04:58 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Inventory\LocationOrgStock\StoreLocationOrgStock;
use Illuminate\Support\Facades\Route;

Route::name('org_stock.')->prefix('org-stock/{orgStock:id}')->group(function () {
    Route::post('location/{location:id}', StoreLocationOrgStock::class)->name('location.store');
});
