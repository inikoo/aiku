<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Enums\Inventory\Location\LocationStatusEnum;
use App\Models\SysAdmin\Organisation;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateWarehouse implements ShouldBeUnique
{
    use AsAction;


    public function handle(Organisation $organisation): void
    {

        $locations           = Location::count();
        $operationalLocations=Location::where('status', LocationStatusEnum::OPERATIONAL)->count();


        $stats  = [
            'number_warehouses'                  => Warehouse::count(),
            'number_warehouse_areas'             => WarehouseArea::count(),
            'number_locations'                   => $locations,
            'number_locations_state_operational' => $operationalLocations,
            'number_locations_state_broken'      => $locations-$operationalLocations
        ];

        $organisation->inventoryStats->update($stats);
    }
}
