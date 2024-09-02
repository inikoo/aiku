<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 18 Apr 2024 09:27:56 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Catalogue;

use Illuminate\Http\Resources\Json\JsonResource;

class ShippingZoneSchemasResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                       => $this->id,
            'slug'                     => $this->slug,
            'name'                     => $this->name,
            'created_at'               => $this->created_at,
        ];
    }
}
