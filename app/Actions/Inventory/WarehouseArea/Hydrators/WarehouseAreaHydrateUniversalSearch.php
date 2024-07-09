<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\WarehouseArea\Hydrators;

use App\Models\Inventory\WarehouseArea;
use Lorisleiva\Actions\Concerns\AsAction;

class WarehouseAreaHydrateUniversalSearch
{
    use AsAction;
    public string $jobQueue = 'universal-search';

    public function handle(WarehouseArea $warehouseArea): void
    {
        $warehouseArea->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $warehouseArea->group_id,
                'organisation_id'   => $warehouseArea->organisation_id,
                'organisation_slug' => $warehouseArea->organisation->slug,
                'warehouse_id'      => $warehouseArea->warehouse_id,
                'warehouse_slug'    => $warehouseArea->warehouse->slug,
                'section'           => 'inventory',
                'title'             => trim($warehouseArea->code.' '.$warehouseArea->name),
            ]
        );
    }

}
