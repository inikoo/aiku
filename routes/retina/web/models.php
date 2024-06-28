<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Feb 2024 23:54:37 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\CRM\Customer\UpdateCustomerSettings;
use App\Actions\Fulfilment\Pallet\DeletePallet;
use App\Actions\Fulfilment\Pallet\ImportPallet;
use App\Actions\Fulfilment\Pallet\StoreMultiplePalletsFromDelivery;
use App\Actions\Fulfilment\Pallet\StorePalletFromDelivery;
use App\Actions\Fulfilment\Pallet\StorePalletToReturn;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\Fulfilment\PalletDelivery\StorePalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\SubmitPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\UpdatePalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\UpdatePalletDeliveryTimeline;
use App\Actions\Fulfilment\PalletReturn\CancelPalletReturn;
use App\Actions\Fulfilment\PalletReturn\DeletePalletFromReturn;
use App\Actions\Fulfilment\PalletReturn\StorePalletReturn;
use App\Actions\Fulfilment\PalletReturn\SubmitPalletReturn;
use App\Actions\Fulfilment\StoredItem\StoreStoredItem;
use App\Actions\Fulfilment\StoredItem\SyncStoredItemToPallet;
use App\Actions\Fulfilment\StoredItemReturn\StoreStoredItemReturn;
use App\Actions\UI\Retina\Profile\UpdateProfile;
use Illuminate\Support\Facades\Route;

Route::patch('/profile', UpdateProfile::class)->name('profile.update');
Route::patch('/settings', UpdateCustomerSettings::class)->name('settings.update');

Route::post('pallet-return', [StorePalletReturn::class, 'fromRetina'])->name('pallet-return.store');
Route::name('pallet-return.')->prefix('pallet-return/{palletReturn:id}')->group(function () {
    Route::post('pallet', [StorePalletToReturn::class, 'fromRetina'])->name('pallet.store');
    Route::post('submit', [SubmitPalletReturn::class, 'fromRetina'])->name('submit');
    Route::post('cancel', [CancelPalletReturn::class, 'fromRetina'])->name('cancel');
    Route::delete('pallet/{pallet:id}', [DeletePalletFromReturn::class, 'fromRetina'])->name('pallet.delete')->withoutScopedBindings();
});

Route::post('pallet-delivery', [StorePalletDelivery::class, 'fromRetina'])->name('pallet-delivery.store');
Route::name('pallet-delivery.')->prefix('pallet-delivery/{palletDelivery:id}')->group(function () {
    Route::post('pallet-upload', [ImportPallet::class,'fromRetina'])->name('pallet.upload');
    Route::post('pallet', [StorePalletFromDelivery::class, 'fromRetina'])->name('pallet.store');
    Route::post('multiple-pallet', [StoreMultiplePalletsFromDelivery::class, 'fromRetina'])->name('multiple-pallets.store');
    Route::patch('update', [UpdatePalletDelivery::class, 'fromRetina'])->name('update');
    Route::patch('update-timeline', [UpdatePalletDeliveryTimeline::class, 'fromRetina'])->name('timeline.update');


    Route::post('submit', SubmitPalletDelivery::class)->name('submit');
});

Route::name('pallet.')->prefix('pallet/{pallet:id}')->group(function () {
    Route::post('stored-items', SyncStoredItemToPallet::class)->name('stored-items.update');

    Route::delete('', [DeletePallet::class, 'fromRetina'])->name('delete');
    Route::patch('', [UpdatePallet::class, 'fromRetina'])->name('update');
});

Route::post('stored-items', [StoreStoredItem::class, 'fromRetina'])->name('stored-items.store');
Route::post('stored-item-returns', [StoreStoredItemReturn::class, 'fromRetina'])->name('stored-item-returns.store');
