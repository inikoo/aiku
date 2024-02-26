<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 29 Jan 2024 10:30:41 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\Hydrators;

use App\Actions\Fulfilment\PalletDelivery\BookInPalletDelivery;
use App\Actions\HydrateModel;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Models\Fulfilment\PalletDelivery;

class HydrateStatePallet extends HydrateModel
{
    use WithActionUpdate;

    public function handle(PalletDelivery $palletDelivery): void
    {
        $palletCount         = $palletDelivery->pallets()->count();
        $palletBookedInCount = $palletDelivery->pallets()->where('state', PalletStateEnum::BOOKED_IN)->count();

        if($palletCount == $palletBookedInCount) {
            BookInPalletDelivery::run($palletDelivery);
        }
    }
}
