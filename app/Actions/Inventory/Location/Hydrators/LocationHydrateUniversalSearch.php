<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\Hydrators;

use App\Models\Inventory\Location;
use Lorisleiva\Actions\Concerns\AsAction;

class LocationHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Location $location): void
    {
        if ($location->trashed()) {
            return;
        }

        $location->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $location->group_id,
                'organisation_id'   => $location->organisation_id,
                'organisation_slug' => $location->organisation->slug,
                'warehouse_id'      => $location->warehouse_id,
                'warehouse_slug'    => $location->warehouse->slug,
                'sections'          => ['inventory'],
                'haystack_tier_1'   => $location->code,
            ]
        );
    }

}
