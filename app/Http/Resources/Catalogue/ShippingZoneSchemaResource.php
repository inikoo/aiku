<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 15:23:04 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Catalogue;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $shop_id
 * @property string $name
 * @property string $slug
 * @property boolean $status
 * @property mixed $created_at
 * @property mixed $updated_at
 *
 */
class ShippingZoneSchemaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'shop_id'    => $this->shop_id,
            'slug'       => $this->slug,
            'name'       => $this->name,
            'status'     => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
