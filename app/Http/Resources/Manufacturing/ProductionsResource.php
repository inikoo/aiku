<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 15 Sept 2022 14:55:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Manufacturing;

use App\Models\Manufacturing\Production;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionsResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Production $production */
        $production=$this;
        return [
            'id'                     => $production->id,
            'slug'                   => $production->slug,
            'code'                   => $production->code,
            'name'                   => $production->name,
        ];
    }
}
