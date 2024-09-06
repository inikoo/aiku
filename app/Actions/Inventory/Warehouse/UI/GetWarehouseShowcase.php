<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 25 May 2023 15:03:06 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Warehouse\UI;

use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\Concerns\AsObject;

class GetWarehouseShowcase
{
    use AsObject;

    /** @noinspection PhpUnusedParameterInspection */
    public function handle(Warehouse $warehouse): array
    {
        return [
            [
                'label'     => __('Warehouse Areas'),
                'icon'      => 'fal fa-map-signs',
                'value'     => $warehouse->stats->number_warehouse_areas
            ],
            [
                'label'     => __('Locations'),
                'icon'      => 'fal fa-inventory',
                'value'     => $warehouse->stats->number_locations
            ],
        ];
    }
}
