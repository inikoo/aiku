<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 22:46:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\WarehouseArea\Hydrators;

use App\Models\Inventory\WarehouseArea;
use Lorisleiva\Actions\Concerns\AsAction;

class WarehouseAreaHydrateLocations
{
    use AsAction;


    public function handle(WarehouseArea $warehouseArea): void
    {
        $numberLocations            = $warehouseArea->locations()->count();
        $numberOperationalLocations = $warehouseArea->locations()->where('status', 'operational')->count();
        $warehouseArea->stats()->update(
            [
                'number_locations'                    => $numberLocations,
                'number_locations_status_operational' => $numberOperationalLocations,
                'number_locations_status_broken'      => $numberLocations - $numberOperationalLocations

            ]
        );
    }
}
