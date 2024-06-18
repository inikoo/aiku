<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Jul 2023 09:57:45 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn\Hydrators;

use App\Actions\HydrateModel;
use App\Models\Fulfilment\PalletReturn;

class HydratePalletReturns extends HydrateModel
{
    public function handle(PalletReturn $palletReturn): void
    {
        $totalPrice = 0;

        $palletReturn->services->each(function ($service) use (&$totalPrice, &$totalQuantity) {
            $totalPrice += $service->price * $service->pivot->quantity;
        });

        $palletReturn->update([
            'number_pallets'             => $palletReturn->pallets->count(),
            'number_pallet_stored_items' => $palletReturn->pallets->sum('number_stored_items'),
            'number_stored_items'        => $palletReturn->pallets->sum('number_stored_items')
        ]);

        $palletReturn->stats()->update([
            'total_price'                => $totalPrice
        ]);
    }
}
