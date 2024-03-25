<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 24 Mar 2024 20:31:47 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Goods;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $code
 * @property number $quantity_in_locations
 * @property number $number_location
 * @property number $unit_value
 * @property string $slug
 * @property string $description
 * @property string $family_slug
 * @property string $family_code
 * @property string $name
 */
class StocksResource extends JsonResource
{
    public function toArray($request): array
    {


        return [
            'slug'               => $this->slug,
            'code'               => $this->code,
            'name'               => $this->name,
            'unit_value'         => $this->unit_value,
            'family_slug'        => $this->family_slug,
            'family_code'        => $this->family_code,
        ];
    }
}
