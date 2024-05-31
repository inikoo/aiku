<?php
/*
 *  Author: Jonathan lopez <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:53:15 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, inikoo
 */

namespace App\Http\Resources\Catalogue;

use App\Http\Resources\Assets\CurrencyResource;
use App\Models\Catalogue\Outer;
use Illuminate\Http\Resources\Json\JsonResource;

class OutersResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Outer $outer */
        $outer=$this;
        return [
            'slug'       => $outer->slug,
            'code'       => $outer->code,
            'name'       => $outer->name,
            'price'      => $outer->price,
            'unit'       => $outer->unit,
            'currency'   => CurrencyResource::make($outer->product->currency),
            'state'      => $outer->state,
        ];
    }
}
