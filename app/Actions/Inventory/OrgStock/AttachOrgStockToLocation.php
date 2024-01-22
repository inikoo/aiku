<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Apr 2023 21:13:56 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock;

use App\Actions\Inventory\Location\Hydrators\LocationHydrateStocks;
use App\Actions\Inventory\Location\Hydrators\LocationHydrateStockValue;
use App\Models\Inventory\Location;
use App\Models\SupplyChain\Stock;
use Lorisleiva\Actions\Concerns\AsAction;

class AttachOrgStockToLocation
{
    use AsAction;

    public function handle(Location $location, Stock $stock, array $modelData): Location
    {
        $location->orgStocks()->attach([$stock->id,$modelData]);

        LocationHydrateStocks::dispatch($location);
        LocationHydrateStockValue::dispatch($location);

        return $location;
    }
}
