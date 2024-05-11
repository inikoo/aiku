<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 15:23:04 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Catalogue;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $shipping_zone_schema_id
 * @property int $shop_id
 * @property string $slug
 * @property string $code
 * @property string $territories
 * @property string $price
 * @property boolean $status
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property string $name
 *
 */
class ShippingZoneResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'shop_id'                 => $this->shop_id,
            'shipping_zone_schema_id' => $this->shipping_zone_schema_id,
            'slug'                    => $this->slug,
            'code'                    => $this->code,
            'name'                    => $this->name,
            'territories'             => $this->territories,
            'price'                   => $this->price,
            'status'                  => $this->status,
            'created_at'              => $this->created_at,
            'updated_at'              => $this->updated_at,
        ];
    }
}
