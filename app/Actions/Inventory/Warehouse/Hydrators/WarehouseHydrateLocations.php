<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 22:40:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Warehouse\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Inventory\Warehouse;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class WarehouseHydrateLocations implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Warehouse $warehouse): void
    {
        $numberLocations            = $warehouse->locations()->count();
        $numberOperationalLocations = $warehouse->locations()->where('status', 'operational')->count();


        $warehouse->stats->update(
            [
                'number_locations'                   => $numberLocations,
                'number_locations_state_operational' => $numberOperationalLocations,
                'number_locations_state_broken'      => $numberLocations - $numberOperationalLocations

            ]
        );
    }

    public function getJobUniqueId(Warehouse $warehouse): int
    {
        return $warehouse->id;
    }
}
