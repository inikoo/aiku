<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 13 Feb 2025 12:34:56 Central Indonesia Time,Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
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
use App\Actions\Fulfilment\PalletDelivery\Json\ShowPalletDeliveryStatus;
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
use Illuminate\Support\Facades\Route;


Route::get('{palletDelivery:id}/status', ShowPalletDeliveryStatus::class)->name('pallet_delivery.status');
