<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Apr 2023 21:13:56 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Stock;

use App\Models\Inventory\Location;
use App\Models\Inventory\Stock;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncLocationStock
{
    use AsAction;

    public function handle(Location $location, Stock $stock): Location
    {
        $stocks = [];

        $stocks[$stock->id] = [
            'quantity' => $stock->quantity,
        ];

        $location->stocks()->sync($stocks);

        return $location;
    }
}
