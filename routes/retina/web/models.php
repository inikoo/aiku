<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Feb 2024 23:54:37 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\CRM\Customer\UpdateCustomerSettings;
use App\Actions\Fulfilment\Pallet\DeletePallet;
use App\Actions\Fulfilment\Pallet\ImportPallet;
use App\Actions\Fulfilment\Pallet\StoreMultiplePallets;
use App\Actions\Fulfilment\Pallet\StorePalletFromDelivery;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\Fulfilment\PalletDelivery\StorePalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\SubmitPalletDelivery;
use App\Actions\UI\Retina\Profile\UpdateProfile;
use Illuminate\Support\Facades\Route;

Route::patch('/profile', UpdateProfile::class)->name('profile.update');
Route::patch('/settings', UpdateCustomerSettings::class)->name('settings.update');

Route::post('pallet-delivery', [StorePalletDelivery::class, 'fromRetina'])->name('pallet-delivery.store');
Route::name('pallet-delivery.')->prefix('pallet-delivery/{palletDelivery:id}')->group(function () {
    Route::post('pallet-upload', [ImportPallet::class,'fromRetina'])->name('pallet.import');
    Route::post('pallet', [StorePalletFromDelivery::class, 'fromRetina'])->name('pallet.store');
    Route::post('multiple-pallet', [StoreMultiplePallets::class, 'fromRetina'])->name('multiple-pallets.store');

    Route::post('submit', SubmitPalletDelivery::class)->name('submit');
});

Route::name('pallet.')->prefix('pallet/{pallet:id}')->group(function () {
    Route::delete('', [DeletePallet::class, 'fromRetina'])->name('delete');
    Route::patch('', [UpdatePallet::class, 'fromRetina'])->name('update');
});
