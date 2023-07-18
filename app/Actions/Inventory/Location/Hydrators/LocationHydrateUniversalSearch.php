<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\Hydrators;

use App\Actions\Traits\WithTenantJob;
use App\Models\Inventory\Location;
use Lorisleiva\Actions\Concerns\AsAction;

class LocationHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Location $location): void
    {
        $location->universalSearch()->updateOrCreate(
            [],
            [
                'section' => 'inventory',
                'title'   => $location->code,
            ]
        );
    }

}
