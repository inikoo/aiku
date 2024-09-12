<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 29 Aug 2024 00:18:41 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Goods\TradeUnit\UpdateTradeUnit;
use Illuminate\Support\Facades\Route;

Route::name('trade-unit.')->prefix('trade-unit/{tradeUnit:id}')->group(function () {
    Route::patch('update', UpdateTradeUnit::class)->name('update')->withoutScopedBindings();
});

