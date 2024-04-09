<?php
/*
 *  Author: Jonathan lopez <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:53:15 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, inikoo
 */

namespace App\Http\Resources\Market;

use App\Models\Market\Outer;
use Illuminate\Http\Resources\Json\JsonResource;

class OuterResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Outer $Outer */
        $Outer=$this;
        return [
            'slug'       => $Outer->slug,
            'code'       => $Outer->code,
            'name'       => $Outer->name,
            'state'      => $Outer->state,
            'created_at' => $Outer->created_at,
            'updated_at' => $Outer->updated_at,
        ];
    }
}
