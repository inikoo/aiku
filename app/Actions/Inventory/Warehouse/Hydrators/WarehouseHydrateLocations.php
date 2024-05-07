<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 22:40:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Warehouse\Hydrators;

use App\Models\Inventory\Warehouse;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class WarehouseHydrateLocations
{
    use AsAction;

    private Warehouse $warehouse;

    public function __construct(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->warehouse->id))->dontRelease()];
    }


    public function handle(Warehouse $warehouse): void
    {
        $numberLocations            = $warehouse->locations()->count();
        $numberOperationalLocations = $warehouse->locations()->where('status', 'operational')->count();


        $warehouse->stats()->update(
            [
                'number_locations'                    => $numberLocations,
                'number_locations_status_operational' => $numberOperationalLocations,
                'number_locations_status_broken'      => $numberLocations - $numberOperationalLocations

            ]
        );
    }
}
