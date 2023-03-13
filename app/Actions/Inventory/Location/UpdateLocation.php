<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 13:05:43 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Inventory\Location;

use App\Actions\Inventory\Location\Hydrators\LocationHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Models\Inventory\Location;

class UpdateLocation
{
    use WithActionUpdate;

    public function handle(Location $location, array $modelData): Location
    {
        $location =  $this->update($location, $modelData, ['data']);

        LocationHydrateUniversalSearch::dispatch($location);

        return $this->update($location, $modelData, ['data']);
    }
}
