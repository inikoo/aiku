<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 19 Oct 2026 10:00:00 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $slug
 * @property mixed $code
 * @property mixed $name
 * @property mixed $stock
 * @property mixed $minimum_stock
 * @property mixed $shortfall
 * @property mixed $family_code
 * @property mixed $family_slug
 */
class OrgStockMinimumStockResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'slug'          => $this->slug,
            'code'          => $this->code,
            'name'          => $this->name,
            'family_code'   => $this->family_code,
            'family_slug'   => $this->family_slug,
            'stock'         => (float) $this->stock,
            'minimum_stock' => $this->minimum_stock !== null ? (float) $this->minimum_stock : null,
            'shortfall'     => $this->shortfall !== null ? max(0, (float) $this->shortfall) : null,
        ];
    }
}
