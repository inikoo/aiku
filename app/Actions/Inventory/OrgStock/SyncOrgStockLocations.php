<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 11:39:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock;

use App\Actions\Inventory\Location\Hydrators\LocationHydrateStocks;
use App\Actions\Inventory\Location\Hydrators\LocationHydrateStockValue;
use App\Models\Inventory\Location;
use App\Models\Inventory\OrgStock;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncOrgStockLocations
{
    use AsAction;

    public function handle(OrgStock $orgStock, array $locationsData): array
    {
        $oldLocations = $orgStock->locations()->pluck('locations.id')->toArray();

        $orgStock->locations()->sync(
            $locationsData
        );

        $newLocations = $orgStock->locations()->pluck('locations.id')->toArray();



        foreach (
            array_unique(
                array_merge($oldLocations, $newLocations)
            ) as $locationID
        ) {
            $location = Location::find($locationID);
            LocationHydrateStocks::dispatch($location);
            LocationHydrateStockValue::dispatch($location);
        }


        return $newLocations;
    }
}
