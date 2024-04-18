<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 18 Apr 2024 09:27:56 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Market;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $code
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property mixed $type
 * @property mixed $asset
 * @property mixed $asset_type
 */
class RentalsResource extends JsonResource
{
    // Note to be used in IndexFulfilments
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'asset_type'    => $this->asset_type,
            'asset'         => $this->asset
        ];
    }
}
