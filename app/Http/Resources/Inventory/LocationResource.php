<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 16 Sept 2022 23:18:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Inventory;

use App\Models\Inventory\Location;
use App\Models\Inventory\LocationOrgStock;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $pivot
 */
class LocationResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Location $location */
        $location = $this;

        return [
            'id'       => $location->id,
            'slug'     => $location->slug,
            'code'     => $location->code,
            'quantity' => $this->whenPivotLoaded(new LocationOrgStock(), function () {
                return $this->pivot->quantity;
            }),
            'tags'     => $location->tags->pluck('slug')->toArray(),
        ];
    }
}
