<?php

/*
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Masters;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property ?string $trade_units
 * @property ?string $master_family_code
 * @property ?int $used_in
 */
class MasterProductsAwaitingBarcodeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,
            'slug'               => $this->slug,
            'code'               => $this->code,
            'name'               => $this->name,
            'trade_units'        => $this->trade_units,
            'master_family_code' => $this->master_family_code,
            'used_in'            => (int)$this->used_in,
        ];
    }
}
