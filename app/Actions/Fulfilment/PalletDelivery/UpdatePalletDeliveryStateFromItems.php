<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Mar 2024 14:25:17 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\HydrateModel;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Models\Fulfilment\PalletDelivery;

class UpdatePalletDeliveryStateFromItems extends HydrateModel
{
    use WithActionUpdate;

    public function handle(PalletDelivery $palletDelivery): void
    {
        $palletCount            = $palletDelivery->pallets()->count();
        $palletBookedInCount    = $palletDelivery->pallets()->where('state', PalletStateEnum::BOOKED_IN)->count();
        $palletNotReceivedCount = $palletDelivery->pallets()->where('state', PalletStateEnum::NOT_RECEIVED)->count();
        $palletReceivedCount    = $palletDelivery->pallets()->where('state', PalletStateEnum::RECEIVED)->count();

        if($palletCount == $palletBookedInCount) {
            BookInPalletDelivery::run($palletDelivery);
        } elseif($palletReceivedCount != 0 || $palletReceivedCount == $palletCount) {
            ReceivedPalletDelivery::run($palletDelivery);
        } elseif($palletNotReceivedCount != 0 || $palletNotReceivedCount == $palletCount) {
            NotReceivedPalletDelivery::run($palletDelivery);
        }

    }
}
