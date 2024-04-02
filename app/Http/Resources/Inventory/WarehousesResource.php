<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 15 Sept 2022 14:55:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Resources\Json\JsonResource;

class WarehousesResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var \App\Models\Inventory\Warehouse $warehouse */
        $warehouse=$this;
        return [
            'id'                     => $warehouse->id,
            'slug'                   => $warehouse->slug,
            'code'                   => $warehouse->code,
            'name'                   => $warehouse->name,
            'number_locations'       => $warehouse->locations()->count(),
            'number_warehouse_areas' => $warehouse->warehouseAreas()->count(),
        ];
    }
}
