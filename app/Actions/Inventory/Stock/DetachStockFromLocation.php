<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 12 May 2023 15:16:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Stock;

use App\Actions\Inventory\Location\HydrateLocation;
use App\Actions\Inventory\Warehouse\HydrateWarehouse;
use App\Models\Inventory\Location;
use Lorisleiva\Actions\Concerns\AsAction;

class DetachStockFromLocation
{
    use AsAction;

    public function handle(Location $location, $stockIds): Location
    {
        $location->stocks()->detach($stockIds);

        HydrateWarehouse::run($location->warehouse);
        HydrateLocation::run($location);

        return $location;
    }
}
