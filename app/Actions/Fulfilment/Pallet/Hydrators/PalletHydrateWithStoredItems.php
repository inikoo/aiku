<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 23:57:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\Hydrators;

use App\Actions\HydrateModel;
use App\Models\Fulfilment\Pallet;

class PalletHydrateWithStoredItems extends HydrateModel
{
    public function handle(Pallet $pallet): void
    {
        $numberStoredItems = $pallet->storedItems()->count();
        $stats             = [
            'with_stored_items' => $numberStoredItems > 0,
        ];

        $pallet->update($stats);
    }
}
