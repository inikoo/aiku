<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Mar 2024 13:28:31 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Inventory;

use App\Models\Inventory\Location;
use App\Models\Inventory\LocationOrgStock;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $pivot
 */
class LocationsResource extends JsonResource
{
    public static $wrap = null;
    public function toArray($request): array
    {
        /** @var Location $location */
        $location = $this;

        return [
            'id'                     => $location->id,
            'slug'                   => $location->slug,
            'code'                   => $location->code,
            'tags'                   => $location->tags()->pluck('slug')->toArray(),
            'allow_stocks'           => $location->allow_stocks,
            'allow_fulfilment'       => $location->allow_fulfilment,
            'allow_dropshipping'     => $location->allow_dropshipping,
            'has_stock_slots'        => $location->has_stock_slots,
            'has_fulfilment'         => $location->has_fulfilment,
            'has_dropshipping_slots' => $location->has_dropshipping_slots,

            'quantity' => $this->whenPivotLoaded(new LocationOrgStock(), function () {
                return $this->pivot->quantity;
            }),
        ];
    }
}
