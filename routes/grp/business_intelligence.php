<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:43:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\UI\BusinessIntelligence\BusinessIntelligenceDashboard;

Route::get('/', [BusinessIntelligenceDashboard::class,'inTenant'])->name('dashboard');


Route::get('/{shop}', [BusinessIntelligenceDashboard::class,'inShop'])->name('shops.show.dashboard');
