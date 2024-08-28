<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 29 Aug 2024 00:18:41 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Inventory\LocationOrgStock\AuditLocationOrgStock;
use App\Actions\Inventory\LocationOrgStock\DeleteLocationOrgStock;
use App\Actions\Inventory\LocationOrgStock\MoveOrgStockToOtherLocation;
use Illuminate\Support\Facades\Route;

Route::name('location_org_stock.')->prefix('location-org-stock/{locationOrgStock:id}')->group(function () {
    Route::delete('', DeleteLocationOrgStock::class)->name('delete');
    Route::patch('audit', AuditLocationOrgStock::class)->name('audit');
    Route::patch('move/{locationOrgStock:id}', MoveOrgStockToOtherLocation::class)->name('move');

});
