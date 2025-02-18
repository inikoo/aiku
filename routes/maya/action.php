<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 26 May 2024 20:27:04 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Dispatching\Picking\UpdatePickingStateToDone;
use App\Actions\Dispatching\Picking\UpdatePickingStateToPacking;
use App\Actions\Dispatching\Picking\UpdatePickingStateToPicked;
use App\Actions\Dispatching\Picking\UpdatePickingStateToPicking;
use App\Actions\Dispatching\Picking\UpdatePickingStateToQueried;
use App\Actions\Dispatching\Picking\UpdatePickingStateToWaiting;
use App\Actions\Fulfilment\Pallet\BookInPallet;
use App\Actions\Fulfilment\Pallet\ReturnPalletToCustomer;
use App\Actions\Fulfilment\Pallet\SetPalletAsDamaged;
use App\Actions\Fulfilment\Pallet\SetPalletAsLost;
use App\Actions\Fulfilment\Pallet\SetPalletAsNotReceived;
use App\Actions\Fulfilment\Pallet\SetPalletInReturnAsPicked;
use App\Actions\Fulfilment\Pallet\SetPalletRental;
use App\Actions\Fulfilment\Pallet\UndoBookedInPallet;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\Fulfilment\Pallet\UpdatePalletLocation;
use App\Actions\Fulfilment\PalletDelivery\ReceivePalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\SetPalletDeliveryAsBookedIn;
use App\Actions\Fulfilment\PalletDelivery\StartBookingPalletDelivery;
use App\Actions\Fulfilment\PalletReturn\ConfirmPalletReturn;
use App\Actions\Fulfilment\PalletReturn\DispatchPalletReturn;
use App\Actions\Fulfilment\PalletReturn\PickedPalletReturn;
use App\Actions\Fulfilment\PalletReturn\PickingPalletReturn;
use App\Actions\Fulfilment\PalletReturnItem\NotPickedPalletFromReturn;
use App\Actions\Fulfilment\PalletReturnItem\UndoPickingPalletFromReturn;
use App\Actions\Fulfilment\StoredItem\UpdateQuantityStoredItemPalletApp;
use App\Actions\UI\Notification\MarkNotificationAsRead;
use App\Actions\UI\Profile\UpdateProfile;

Route::patch('pallet/{pallet:id}/location/{location:slug}/move', [UpdatePalletLocation::class,'usingLocationSlug'])->name('move_pallet')->withoutScopedBindings();
Route::patch('pallet/{pallet:id}/location/{location:slug}/book-in', [BookInPallet::class,'usingLocationSlug'])->name('bookin_pallet')->withoutScopedBindings();





Route::patch('pallet/{pallet:id}/return', ReturnPalletToCustomer::class)->name('pallet.return');
Route::patch('pallet/{pallet:id}', [UpdatePallet::class, 'fromApi'])->name('pallet.update');
Route::patch('pallet/{pallet:id}/not-received', SetPalletAsNotReceived::class)->name('pallet.not-received');
Route::patch('pallet/{pallet:id}/undo-not-received', UndoBookedInPallet::class)->name('pallet.undo-not-received');

Route::patch('pallet/{pallet:id}/damaged', SetPalletAsDamaged::class)->name('pallet.damaged');
Route::patch('pallet/{pallet:id}/lost', SetPalletAsLost::class)->name('pallet.lost');

Route::patch('pallet/{pallet:id}/set-rental', SetPalletRental::class)->name('pallet.set-rental');

Route::prefix('pallet/{pallet:id}')->group(function () {
    Route::patch('stored-item/{storedItem:id}', UpdateQuantityStoredItemPalletApp::class)->name('pallet.sync')->withoutScopedBindings();
});

Route::post('profile', UpdateProfile::class)->name('profile.update');
Route::patch('notification/{notification}', MarkNotificationAsRead::class)->name('notifications.read');


Route::patch('pallet-delivery/{palletDelivery:id}/received', ReceivePalletDelivery::class)->name('pallet-delivery.received');
Route::patch('pallet-delivery/{palletDelivery:id}/start-booking', StartBookingPalletDelivery::class)->name('pallet-delivery.start_booking');
Route::patch('pallet-delivery/{palletDelivery:id}/booked-in', SetPalletDeliveryAsBookedIn::class)->name('pallet-delivery.booked-in');

Route::patch('pallet-return/{palletReturn:id}/confirm', [ConfirmPalletReturn::class, 'maya'])->name('pallet-return.confirm');
Route::patch('pallet-return/{palletReturn:id}/start-picking', [PickingPalletReturn::class, 'maya'])->name('pallet-return.picking');
Route::patch('pallet-return/{palletReturn:id}/picked', [PickedPalletReturn::class, 'maya'])->name('pallet-return.picked');
Route::patch('pallet-return/{palletReturn:id}/dispatch', [DispatchPalletReturn::class, 'maya'])->name('pallet-return.dispatch');

Route::patch('pallet-return-item/{palletReturnItem:id}/pick', SetPalletInReturnAsPicked::class)->name('pallet-return-item.pick');
Route::patch('pallet-return-item/{palletReturnItem:id}/undo-pick', UndoPickingPalletFromReturn::class)->name('pallet-return-item.undo-pick');
Route::patch('pallet-return-item/{palletReturnItem:id}/not-picked', NotPickedPalletFromReturn::class)->name('pallet-return-item.not-picked');

Route::patch('pickings/{picking:id}/picking', UpdatePickingStateToPicking::class)->name('pickings.picking');
Route::patch('pickings/{picking:id}/queried', UpdatePickingStateToQueried::class)->name('pickings.queried');
Route::patch('pickings/{picking:id}/waiting', UpdatePickingStateToWaiting::class)->name('pickings.waiting');
Route::patch('pickings/{picking:id}/picked', UpdatePickingStateToPicked::class)->name('pickings.picked');
Route::patch('pickings/{picking:id}/packing', UpdatePickingStateToPacking::class)->name('pickings.packing');
Route::patch('pickings/{picking:id}/done', UpdatePickingStateToDone::class)->name('pickings.done');

require __DIR__."/actions/inventory/location_org_stock.php";
