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
 * @property string $slug
 * @property mix $net_weight
 * @property string $type
 * @property string $name
 */
class TradeUnitResource extends JsonResource
{
    public function toArray($request): array
    {


        return [
            'slug'               => $this->slug,
            'code'               => $this->code,
            'name'               => $this->name,
            'description'        => $this->description,
            'barcode'            => $this->barcode,
            'gross_weight'       => $this->gross_weight,
            'net_weight'         => $this->net_weight,
            'dimensions'         => $this->dimensions,
            'volume'             => $this->volume,
            'type'               => $this->type,
            'image_id'           => $this->image_id
        ];
    }
}
