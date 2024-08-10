<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Aug 2024 21:46:33 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\WarehouseArea\Search;

use App\Models\Inventory\WarehouseArea;
use Lorisleiva\Actions\Concerns\AsAction;

class WarehouseAreaRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(WarehouseArea $warehouseArea): void
    {
        if ($warehouseArea->trashed()) {
            $warehouseArea->universalSearch()->delete();

            return;
        }

        $warehouseArea->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $warehouseArea->group_id,
                'organisation_id'   => $warehouseArea->organisation_id,
                'organisation_slug' => $warehouseArea->organisation->slug,
                'warehouse_id'      => $warehouseArea->warehouse_id,
                'warehouse_slug'    => $warehouseArea->warehouse->slug,
                'sections'          => ['inventory'],
                'haystack_tier_1'   => trim($warehouseArea->code.' '.$warehouseArea->name),
                'keyword'           => $warehouseArea->code,
            ]
        );
    }

}
