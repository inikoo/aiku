<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 12 May 2023 15:16:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock;

use App\Actions\Inventory\Location\Hydrators\LocationHydrateStocks;
use App\Actions\Inventory\Location\Hydrators\LocationHydrateStockValue;
use App\Models\Inventory\Location;
use App\Models\Inventory\OrgStock;
use Lorisleiva\Actions\Concerns\AsAction;

class DetachOrgStockFromLocation
{
    use AsAction;

    public function handle(Location $location, OrgStock $orgStock): Location
    {
        $location->orgStocks()->detach($orgStock->id);

        LocationHydrateStocks::run($location);
        LocationHydrateStockValue::run($location);

        return $location;
    }
}
