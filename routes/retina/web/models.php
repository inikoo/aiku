<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Feb 2024 23:54:37 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\CRM\Customer\DeleteCustomerDeliveryAddress;
use App\Actions\CRM\Customer\UpdateCustomerDeliveryAddress;
use App\Actions\CRM\Customer\UpdateCustomerSettings;
use App\Actions\Fulfilment\FulfilmentCustomer\AddDeliveryAddressToFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentTransaction\DeleteFulfilmentTransaction;
use App\Actions\Fulfilment\FulfilmentTransaction\StoreFulfilmentTransaction;
use App\Actions\Fulfilment\FulfilmentTransaction\UpdateFulfilmentTransaction;
use App\Actions\Fulfilment\Pallet\DeletePallet;
use App\Actions\Fulfilment\Pallet\ImportPallet;
use App\Actions\Fulfilment\Pallet\StoreMultiplePalletsFromDelivery;
use App\Actions\Fulfilment\Pallet\StorePalletFromDelivery;
use App\Actions\Fulfilment\Pallet\AttachPalletsToReturn;
use App\Actions\Fulfilment\Pallet\ImportStoredItem;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\Fulfilment\PalletDelivery\Pdf\PdfPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\StorePalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\SubmitPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\UpdatePalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\UpdatePalletDeliveryTimeline;
use App\Actions\Fulfilment\PalletReturn\CancelPalletReturn;
use App\Actions\Fulfilment\PalletReturn\DetachPalletFromReturn;
use App\Actions\Fulfilment\PalletReturn\StorePalletReturn;
use App\Actions\Fulfilment\PalletReturn\SubmitPalletReturn;
use App\Actions\Fulfilment\PalletReturn\UpdatePalletReturn;
use App\Actions\Fulfilment\StoredItem\StoreStoredItem;
use App\Actions\Fulfilment\StoredItem\SyncStoredItemToPallet;
use App\Actions\UI\Retina\Profile\UpdateProfile;
use Illuminate\Support\Facades\Route;

Route::patch('/profile', UpdateProfile::class)->name('profile.update');
Route::patch('/settings', UpdateCustomerSettings::class)->name('settings.update');

Route::name('fulfilment-transaction.')->prefix('fulfilment_transaction/{fulfilmentTransaction:id}')->group(function () {
    Route::patch('', [UpdateFulfilmentTransaction::class,'fromRetina'])->name('update');
    Route::delete('', [DeleteFulfilmentTransaction::class,'fromRetina'])->name('delete');
});

Route::post('pallet-return', [StorePalletReturn::class, 'fromRetina'])->name('pallet-return.store');
Route::post('pallet-return/stored-items', [StorePalletReturn::class, 'fromRetinaWithStoredItems'])->name('pallet-return-stored-items.store');
Route::name('pallet-return.')->prefix('pallet-return/{palletReturn:id}')->group(function () {
    Route::post('stored-item-upload', [ImportStoredItem::class, 'fromRetina'])->name('stored-item.upload');
    Route::post('pallet', [AttachPalletsToReturn::class, 'fromRetina'])->name('pallet.store');
    Route::patch('update', [UpdatePalletReturn::class, 'fromRetina'])->name('update');
    Route::post('submit', [SubmitPalletReturn::class, 'fromRetina'])->name('submit');
    Route::post('cancel', [CancelPalletReturn::class, 'fromRetina'])->name('cancel');
    Route::delete('pallet/{pallet:id}', [DetachPalletFromReturn::class, 'fromRetina'])->name('pallet.delete')->withoutScopedBindings();
    Route::post('transaction', [StoreFulfilmentTransaction::class,'fromRetinaInPalletReturn'])->name('transaction.store');
});



Route::post('pallet-delivery', [StorePalletDelivery::class, 'fromRetina'])->name('pallet-delivery.store');
Route::name('pallet-delivery.')->prefix('pallet-delivery/{palletDelivery:id}')->group(function () {
    Route::post('pallet-upload', [ImportPallet::class,'fromRetina'])->name('pallet.upload');
    Route::post('pallet', [StorePalletFromDelivery::class, 'fromRetina'])->name('pallet.store');
    Route::post('multiple-pallet', [StoreMultiplePalletsFromDelivery::class, 'fromRetina'])->name('multiple-pallets.store');
    Route::patch('update', [UpdatePalletDelivery::class, 'fromRetina'])->name('update');
    Route::patch('update-timeline', [UpdatePalletDeliveryTimeline::class, 'fromRetina'])->name('timeline.update');
    Route::post('transaction', [StoreFulfilmentTransaction::class,'fromRetinaInPalletDelivery'])->name('transaction.store');
    Route::post('submit', SubmitPalletDelivery::class)->name('submit');
    Route::get('pdf', PdfPalletDelivery::class)->name('pdf');
});

Route::name('pallet.')->prefix('pallet/{pallet:id}')->group(function () {
    Route::post('stored-items', SyncStoredItemToPallet::class)->name('stored-items.update');

    Route::delete('', [DeletePallet::class, 'fromRetina'])->name('delete');
    Route::patch('', [UpdatePallet::class, 'fromRetina'])->name('update');
});

Route::post('stored-items', [StoreStoredItem::class, 'fromRetina'])->name('stored-items.store');

Route::name('customer.')->prefix('customer/{customer:id}')->group(function () {
    Route::patch('delivery-address/update', [UpdateCustomerDeliveryAddress::class, 'fromRetina'])->name('delivery-address.update');
    Route::delete('delivery-address/{address:id}/delete', [DeleteCustomerDeliveryAddress::class, 'fromRetina'])->name('delivery-address.delete');
});

Route::name('fulfilment-customer.')->prefix('fulfilment-customer/{fulfilmentCustomer:id}')->group(function () {
    Route::post('delivery-address/store', [AddDeliveryAddressToFulfilmentCustomer::class, 'fromRetina'])->name('delivery-address.store');
});
