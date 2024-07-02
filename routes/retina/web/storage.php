<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 23 Feb 2024 09:01:56 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentPhysicalGoods;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentServices;
use App\Actions\Fulfilment\Pallet\UI\ShowPallet;
use App\Actions\Fulfilment\PalletReturn\IndexStoredPallets;
use App\Actions\Fulfilment\StoredItem\UI\IndexBookedInStoredItems;
use App\Actions\Fulfilment\StoredItemReturn\StoreStoredItemToStoredItemReturn;
use App\Actions\Retina\Storage\Pallet\UI\IndexPallets;
use App\Actions\Retina\Storage\PalletDelivery\UI\IndexPalletDeliveries;
use App\Actions\Retina\Storage\PalletDelivery\UI\ShowPalletDelivery;
use App\Actions\Retina\Storage\PalletReturn\UI\IndexPalletReturns;
use App\Actions\Retina\Storage\PalletReturn\UI\ShowPalletReturn;
use App\Actions\Retina\Storage\StoredItemReturn\UI\IndexStoredItemReturns;
use App\Actions\Retina\Storage\StoredItemReturn\UI\ShowStoredItemReturn;
use App\Actions\Retina\Storage\StoredItems\UI\IndexStoredItems;
use App\Actions\UI\Retina\Storage\UI\ShowStorageDashboard;

Route::get('/dashboard', ShowStorageDashboard::class)->name('dashboard');

Route::prefix('pallet-deliveries')->as('pallet-deliveries.')->group(function () {
    Route::get('', IndexPalletDeliveries::class)->name('index');
    Route::get('{palletDelivery}', ShowPalletDelivery::class)->name('show');
    Route::get('{palletDelivery}/pallets/{pallet}', [ShowPallet::class, 'inFulfilmentCustomer'])->name('pallets.show');
});

Route::prefix('pallet-returns')->as('pallet-returns.')->group(function () {
    Route::get('', IndexPalletReturns::class)->name('index');
    Route::get('{palletReturn}', ShowPalletReturn::class)->name('show');
    Route::get('{palletReturn}/pallets/{pallet}', [ShowPallet::class, 'inFulfilmentCustomer'])->name('pallets.show');
});

Route::get('pallets', IndexPallets::class)->name('pallets.index');
Route::get('stored-pallets', [IndexStoredPallets::class, 'fromRetina'])->name('stored-pallets.index');
Route::get('pallets/{pallet}', [ShowPallet::class, 'inFulfilmentCustomer'])->name('pallets.show');
Route::get('stored-items', IndexStoredItems::class)->name('stored-items.index');

Route::prefix('stored-item-returns')->as('stored-item-returns.')->group(function () {
    Route::get('booked-in-stored-items', [IndexBookedInStoredItems::class, 'fromRetina'])->name('booked-in.index');
    Route::get('stored-items', IndexStoredItemReturns::class)->name('index');
    Route::get('{storedItemReturn}', ShowStoredItemReturn::class)->name('show');
    Route::post('{storedItemReturn}/stored-item', [StoreStoredItemToStoredItemReturn::class, 'fromRetina'])->name('stored-item.store');
});

Route::get('services', [IndexFulfilmentServices::class, 'fromRetina'])->name('services.index');
Route::get('physical-goods', [IndexFulfilmentPhysicalGoods::class, 'fromRetina'])->name('outers.index');
