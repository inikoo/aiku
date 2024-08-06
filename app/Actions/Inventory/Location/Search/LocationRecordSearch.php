<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 06 Aug 2024 16:04:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location\Search;

use App\Models\Inventory\Location;
use Lorisleiva\Actions\Concerns\AsAction;

class LocationRecordSearch
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
                'keyword'           => $location->code,
            ]
        );
    }

}
