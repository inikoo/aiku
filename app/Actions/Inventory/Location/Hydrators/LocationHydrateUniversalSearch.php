<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Inventory\Location;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class LocationHydrateUniversalSearch implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Location $location): void
    {
        $location->universalSearch()->create(
            [
                'primary_term'   => $location->warehouse_id,
                'secondary_term' => $location->warehouse_area_id
            ]
        );
    }

    public function getJobUniqueId(Location $location): int
    {
        return $location->id;
    }
}
