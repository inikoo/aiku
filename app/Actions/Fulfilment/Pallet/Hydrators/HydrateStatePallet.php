<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 29 Jan 2024 10:30:41 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\Hydrators;

use App\Actions\Fulfilment\PalletDelivery\BookInPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\NotReceivedPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\ReceivedPalletDelivery;
use App\Actions\Fulfilment\PalletReturn\PickedPalletReturn;
use App\Actions\HydrateModel;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;

class HydrateStatePallet extends HydrateModel
{
    use WithActionUpdate;

    public function handle(PalletDelivery|PalletReturn $parent): void
    {
        $palletCount            = $parent->pallets()->count();
        $palletBookedInCount    = $parent->pallets()->where('state', PalletStateEnum::BOOKED_IN)->count();
        $palletNotReceivedCount = $parent->pallets()->where('state', PalletStateEnum::NOT_RECEIVED)->count();
        $palletReceivedCount    = $parent->pallets()->where('state', PalletStateEnum::RECEIVED)->count();

        $palletPickedCount      = $parent->pallets()->where('state', PalletStateEnum::PICKED)->count();
        $palletNotPickedCount   = $parent->pallets()->where('state', PalletStateEnum::NOT_PICKED)->count();

        if ($parent instanceof PalletDelivery) {
            if($palletCount == $palletBookedInCount) {
                BookInPalletDelivery::run($parent);
            } elseif($palletReceivedCount != 0 || $palletReceivedCount == $palletCount) {
                ReceivedPalletDelivery::run($parent);
            } elseif($palletNotReceivedCount != 0 || $palletNotReceivedCount == $palletCount) {
                NotReceivedPalletDelivery::run($parent);
            }
        } else {
            if(($palletPickedCount + $palletNotPickedCount) == $palletCount) {
                PickedPalletReturn::run($parent);
            }
        }
    }
}
