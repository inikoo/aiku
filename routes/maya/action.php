<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 26 May 2024 20:27:04 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Fulfilment\Pallet\ReturnPalletToCustomer;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\Fulfilment\Pallet\UpdatePalletLocation;
use App\Actions\Fulfilment\PalletDelivery\ReceivedPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\StartBookingPalletDelivery;
use App\Actions\UI\Notification\MarkNotificationAsRead;
use App\Actions\UI\Profile\UpdateProfile;

Route::patch('location/{location:id}/pallet/{pallet:id}', UpdatePalletLocation::class)->name('pallet.location.update')->withoutScopedBindings();
Route::patch('pallet/{pallet:id}/return', ReturnPalletToCustomer::class)->name('pallet.return');
Route::patch('pallet/{pallet:id}', [UpdatePallet::class, 'fromApi'])->name('pallet.update');

Route::post('profile', UpdateProfile::class)->name('profile.update');
Route::patch('notification/{notification}', MarkNotificationAsRead::class)->name('notifications.read');


Route::patch('pallet-delivery/{palletDelivery:id}/received', ReceivedPalletDelivery::class)->name('pallet-delivery.received');
Route::patch('pallet-delivery/{palletDelivery:id}/start-booking', StartBookingPalletDelivery::class)->name('pallet-delivery.start_booking');
