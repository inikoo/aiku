<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 16 Sept 2022 23:18:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Inventory;

use App\Models\Inventory\LocationStock;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $code
 * @property mixed $pivot
 * @property int $id
 */
class LocationResource extends JsonResource
{

    public function toArray($request): array
    {

        return [
            'id'=>$this->id,
            'code'=>$this->code,
            'quantity' => $this->whenPivotLoaded(new LocationStock(), function () {
                return $this->pivot->quantity;
            }),
        ];
    }
}
