<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 17:07:24 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant\Hydrators;

use App\Models\Central\Tenant;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\Inventory\WarehouseStats;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class TenantHydrateWarehouse implements ShouldBeUnique
{
    use AsAction;
    use HasTenantHydrate;

    public function handle(Tenant $tenant): void
    {
        $stats  = [
            'number_warehouses'                  => Warehouse::count(),
            'number_warehouse_areas'             => WarehouseArea::count(),
            'number_locations'                   => WarehouseStats::sum('number_locations'),
            'number_locations_state_operational' => WarehouseStats::sum('number_locations_state_operational'),
            'number_locations_state_broken'      => WarehouseStats::sum('number_locations_state_broken'),
        ];

        $tenant->inventoryStats->update($stats);
    }
}
