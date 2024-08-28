<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 29 Aug 2024 00:20:51 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Inventory\LocationOrgStock\DeleteLocationOrgStock;
use Illuminate\Support\Facades\Route;

Route::name('location_org_stock.')->prefix('location-org-stock/{locationOrgStock:id}')->group(function () {
    Route::delete('', DeleteLocationOrgStock::class)->name('delete');
});
