<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jan 2024 12:37:34 Malaysia Time, Bali Office, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\Pallet\UI\EditPallet;
use App\Actions\Devel\UI\IndexDummies;
use App\Actions\Fulfilment\Pallet\UI\IndexPalletsInWarehouse;
use App\Actions\Fulfilment\Pallet\UI\IndexReturnedPalletsInWarehouse;
use App\Actions\Fulfilment\Pallet\UI\ShowPallet;
use App\Actions\Fulfilment\PalletDelivery\UI\IndexPalletDeliveries;
use App\Actions\Fulfilment\PalletDelivery\UI\ShowPalletDelivery;
use App\Actions\Fulfilment\PalletReturn\UI\IndexPalletReturns;
use App\Actions\Fulfilment\PalletReturn\UI\ShowPalletReturn;
use App\Actions\UI\Fulfilment\ShowFulfilmentDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowFulfilmentDashboard::class)->name('dashboard');

Route::get('returned-pallets', IndexReturnedPalletsInWarehouse::class)->name('returned_pallets.index');
Route::get('damaged-pallets', IndexDummies::class)->name('damaged_pallets.index');
Route::get('lost-pallets', IndexDummies::class)->name('lost_pallets.index');

Route::prefix('pallets')->as('pallets.')->group(function () {
    Route::get('', IndexPalletsInWarehouse::class)->name('index');
    Route::get('{pallet}', [ShowPallet::class, 'inWarehouse'])->name('show');
Route::get('/pallets/{pallet}/edit', [EditPallet::class, 'inWarehouse'])->name('edit');
});


Route::get('deliveries', [IndexPalletDeliveries::class, 'inWarehouse'])->name('pallet-deliveries.index');
Route::get('deliveries/{palletDelivery}', [ShowPalletDelivery::class, 'inWarehouse'])->name('pallet-deliveries.show');

Route::get('returns', [IndexPalletReturns::class, 'inWarehouse'])->name('pallet-returns.index');
Route::get('returns/{palletReturn}', [ShowPalletReturn::class, 'inWarehouse'])->name('pallet-returns.show');
