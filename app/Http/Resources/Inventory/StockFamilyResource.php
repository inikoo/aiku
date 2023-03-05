<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 08:17:00 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $code
 * @property string $slug
 * @property number $number_stocks
 * @property int $id
 * @property string $name
 */
class StockFamilyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug'          => $this->slug,
            'code'          => $this->code,
            'name'          => $this->name,
            'number_stocks' => $this->number_stocks,
        ];
    }
}
