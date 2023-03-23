<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:52:52 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $code
 * @property number $quantity
 * @property string $slug
 * @property string $description
 * @property string $family_slug
 * @property string $family_code
 */
class StockResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug'        => $this->slug,
            'code'        => $this->code,
            'description' => $this->description,
            'quantity'    => $this->quantity,
            'family_slug' => $this->family_slug,
            'family_code' => $this->family_code,
            'locations'   => LocationResource::collection($this->whenLoaded('locations')),
        ];
    }
}
