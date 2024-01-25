<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Jul 2023 09:57:45 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\Hydrators;

use App\Actions\HydrateModel;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\FulfilmentOrder;
use App\Models\Fulfilment\Pallet;
use Illuminate\Support\Collection;

class HydrateQuantityPalletStoredItems extends HydrateModel
{
    use WithActionUpdate;

    public function handle(Pallet $pallet): void
    {
        $this->update($pallet, [
            'items_quantity' => $pallet->storedItems()->sum('quantity')
        ]);
    }
}
