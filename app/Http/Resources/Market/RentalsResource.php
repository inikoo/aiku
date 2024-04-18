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
 * @property mixed $auto_assign_asset
 * @property mixed $auto_assign_asset_type
 */
class RentalsResource extends JsonResource
{
    // Note to be used in IndexFulfilments
    public function toArray($request): array
    {
        return [
            'id'                        => $this->id,
            'name'                      => $this->name,
            'auto_assign_asset_type'    => $this->auto_assign_asset_type,
            'auto_assign_asset'         => $this->auto_assign_asset
        ];
    }
}
