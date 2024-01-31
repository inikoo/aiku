<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jan 2024 11:31:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\Pallet\UI\CreatePallet;
use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Fulfilment\PalletDelivery\UI\IndexPalletDeliveries;
use App\Actions\Fulfilment\PalletDelivery\UI\ShowPalletDelivery;

Route::get('', ShowFulfilment::class)->name('dashboard');

Route::get('/pallets', IndexPallets::class)->name('pallets.index');
Route::get('/pallets/create', CreatePallet::class)->name('pallets.create');
Route::get('deliveries', IndexPalletDeliveries::class)->name('pallet-deliveries.index');
Route::get('deliveries/{palletDelivery}', ShowPalletDelivery::class)->name('pallet-deliveries.show');
