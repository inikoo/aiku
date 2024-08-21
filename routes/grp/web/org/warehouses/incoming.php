<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Aug 2024 09:40:59 Central Indonesia Time, Bali Office, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\PalletDelivery\UI\IndexPalletDeliveries;
use App\Actions\Fulfilment\PalletDelivery\UI\ShowPalletDelivery;
use App\Stubs\UIDummies\ShowDummyDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowDummyDashboard::class)->name('backlog');



Route::get('fulfilment-deliveries', [IndexPalletDeliveries::class, 'inWarehouse'])->name('pallet-deliveries.index');
Route::get('fulfilment-deliveries/{palletDelivery}', [ShowPalletDelivery::class, 'inWarehouse'])->name('pallet-deliveries.show');
