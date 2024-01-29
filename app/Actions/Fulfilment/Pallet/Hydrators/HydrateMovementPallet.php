<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 29 Jan 2024 10:30:41 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\Hydrators;

use App\Actions\HydrateModel;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\Pallet;

class HydrateMovementPallet extends HydrateModel
{
    use WithActionUpdate;

    public function handle(Pallet $pallet, $lastLocationId): void
    {
        $pallet->movements()->create([
            'location_from_id' => $lastLocationId,
            'location_to_id'   => $pallet->location_id,
            'moved_at' => now()
        ]);
    }
}
