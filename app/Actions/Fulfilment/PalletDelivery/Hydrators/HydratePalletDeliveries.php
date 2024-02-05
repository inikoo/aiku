<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Jul 2023 09:57:45 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery\Hydrators;

use App\Actions\HydrateModel;
use App\Models\Fulfilment\PalletDelivery;

class HydratePalletDeliveries extends HydrateModel
{
    public function handle(PalletDelivery $palletDelivery): void
    {
        $palletDelivery->update([
            'number_pallets' => $palletDelivery->pallets->count(),
            'number_pallet_stored_items' => $palletDelivery->pallets->sum('number_stored_items'),
            'number_stored_items' => $palletDelivery->pallets->sum('number_stored_items')
        ]);
    }
}
