<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Feb 2024 23:54:37 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\CRM\Customer\DeleteCustomerDeliveryAddress;
use App\Actions\CRM\Customer\UpdateCustomerDeliveryAddress;
use App\Actions\CRM\WebUser\StoreWebUser;
use App\Actions\CRM\WebUser\UpdateWebUser;
use App\Actions\Dropshipping\CustomerClient\StoreCustomerClient;
use App\Actions\Dropshipping\Shopify\Product\HandleApiDeleteProductFromShopify;
use App\Actions\Dropshipping\Shopify\Product\StoreProductShopify;
use App\Actions\Fulfilment\FulfilmentCustomer\AddDeliveryAddressToFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\DeletePallet;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\Fulfilment\StoredItem\StoreStoredItem;
use App\Actions\Fulfilment\StoredItem\SyncStoredItemToPallet;
use App\Actions\Retina\CRM\RetinaUpdateCustomerSettings;
use App\Actions\Retina\Fulfilment\RetinaDeleteFulfilmentTransaction;
use App\Actions\Retina\Fulfilment\RetinaUpdateFulfilmentTransaction;
use App\Actions\Retina\Storage\FulfilmentTransaction\StoreRetinaFulfilmentTransaction;
use App\Actions\Retina\Storage\Pallet\RetinaImportPallet;
use App\Actions\Retina\Storage\Pallet\RetinaStoreMultiplePalletsFromDelivery;
use App\Actions\Retina\Storage\Pallet\RetinaStorePalletFromDelivery;
use App\Actions\Retina\Storage\PalletDelivery\Pdf\RetinaPdfPalletDelivery;
use App\Actions\Retina\Storage\PalletDelivery\StoreRetinaPalletDelivery;
use App\Actions\Retina\Storage\PalletDelivery\SubmitRetinaPalletDelivery;
use App\Actions\Retina\Storage\PalletDelivery\UpdateRetinaPalletDelivery;
use App\Actions\Retina\Storage\PalletDelivery\UpdateRetinaPalletDeliveryTimeline;
use App\Actions\Retina\Storage\PalletReturn\AttachRetinaPalletsToReturn;
use App\Actions\Retina\Storage\PalletReturn\CancelRetinaPalletReturn;
use App\Actions\Retina\Storage\PalletReturn\DetachRetinaPalletFromReturn;
use App\Actions\Retina\Storage\PalletReturn\ImportRetinaPalletReturnItem;
use App\Actions\Retina\Storage\PalletReturn\StoreRetinaPalletReturn;
use App\Actions\Retina\Storage\PalletReturn\StoreRetinaStoredItemsToReturn;
use App\Actions\Retina\Storage\PalletReturn\SubmitRetinaPalletReturn;
use App\Actions\Retina\Storage\PalletReturn\UpdateRetinaPalletReturn;
use App\Actions\UI\Retina\Profile\RetinaUpdateProfile;
use App\Actions\UI\Retina\SysAdmin\UpdateRetinaFulfilmentCustomer;
use Illuminate\Support\Facades\Route;

Route::patch('/profile', RetinaUpdateProfile::class)->name('profile.update');
Route::patch('/settings', RetinaUpdateCustomerSettings::class)->name('settings.update');

Route::name('fulfilment-transaction.')->prefix('fulfilment_transaction/{fulfilmentTransaction:id}')->group(function () {
    Route::patch('', RetinaUpdateFulfilmentTransaction::class)->name('update');
    Route::delete('', RetinaDeleteFulfilmentTransaction::class)->name('delete');
});

Route::post('pallet-return', StoreRetinaPalletReturn::class)->name('pallet-return.store');
Route::post('pallet-return/stored-items', [StoreRetinaPalletReturn::class, 'fromRetinaWithStoredItems'])->name('pallet-return-stored-items.store');
Route::name('pallet-return.')->prefix('pallet-return/{palletReturn:id}')->group(function () {
    Route::post('stored-item-upload', ImportRetinaPalletReturnItem::class)->name('stored-item.upload');
    Route::post('stored-item', StoreRetinaStoredItemsToReturn::class)->name('stored_item.store');
    Route::post('pallet', AttachRetinaPalletsToReturn::class)->name('pallet.store');
    Route::patch('update', UpdateRetinaPalletReturn::class)->name('update');
    Route::post('submit', SubmitRetinaPalletReturn::class)->name('submit');
    Route::post('cancel', CancelRetinaPalletReturn::class)->name('cancel');
    Route::delete('pallet/{pallet:id}', DetachRetinaPalletFromReturn::class)->name('pallet.delete')->withoutScopedBindings();
    Route::post('transaction', [StoreRetinaFulfilmentTransaction::class, 'fromRetinaInPalletReturn'])->name('transaction.store');
});



Route::post('pallet-delivery', StoreRetinaPalletDelivery::class)->name('pallet-delivery.store');
Route::name('pallet-delivery.')->prefix('pallet-delivery/{palletDelivery:id}')->group(function () {
    Route::post('pallet-upload', RetinaImportPallet::class)->name('pallet.upload');
    Route::post('pallet', RetinaStorePalletFromDelivery::class)->name('pallet.store');
    Route::post('multiple-pallet', RetinaStoreMultiplePalletsFromDelivery::class)->name('multiple-pallets.store');
    Route::patch('update', UpdateRetinaPalletDelivery::class)->name('update');
    Route::patch('update-timeline', UpdateRetinaPalletDeliveryTimeline::class)->name('timeline.update');
    Route::post('transaction', [StoreRetinaFulfilmentTransaction::class,'fromRetinaInPalletDelivery'])->name('transaction.store');
    Route::post('submit', SubmitRetinaPalletDelivery::class)->name('submit');
    Route::get('pdf', RetinaPdfPalletDelivery::class)->name('pdf');
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
    Route::patch('update', UpdateRetinaFulfilmentCustomer::class)->name('update');
    Route::post('delivery-address/store', [AddDeliveryAddressToFulfilmentCustomer::class, 'fromRetina'])->name('delivery-address.store');
});

Route::post('customer-client', [StoreCustomerClient::class, 'fromRetina'])->name('customer-client.store');

Route::name('dropshipping.')->prefix('dropshipping')->group(function () {
    Route::post('shopify-user/{shopifyUser:id}/products', StoreProductShopify::class)->name('shopify_user.product.store')->withoutScopedBindings();
    Route::delete('shopify-user/{shopifyUser:id}/products/{product}', HandleApiDeleteProductFromShopify::class)->name('shopify_user.product.delete')->withoutScopedBindings();
});

Route::name('web-users.')->prefix('web-users')->group(function () {
    Route::post('', [StoreWebUser::class, 'inRetina'])->name('store');
    Route::patch('{webUser:id}/update', [UpdateWebUser::class, 'inRetina'])->name('update');
});
