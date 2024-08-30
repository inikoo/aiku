<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 08:17:00 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Goods;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $slug
 * @property string $code
 * @property number $state
 * @property string $name
 * @property string $description
 * @property string $number_current_stocks
 * @property mixed $created_at
 * @property mixed $updated_at
 *
 */
class StockFamiliesResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug'                  => $this->slug,
            'code'                  => $this->code,
            'state'                 => $this->state,
            'name'                  => $this->name,
            'number_current_stocks' => $this->number_current_stocks,
        ];
    }
}
