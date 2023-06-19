<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Inventory\Location;
use Lorisleiva\Actions\Concerns\AsAction;

class LocationHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Location $location): void
    {
        $location->universalSearch()->create(
            [
                'section' => 'Inventory',
                'route' => json_encode([
                    'name'      => 'inventory.warehouses.show.locations.show',
                    'arguments' => [
                        $location->warehouse->slug,
                        $location->slug
                    ]
                ]),
                'icon' => 'fa-inventory',
                'primary_term'   => $location->code,
            ]
        );
    }

}
