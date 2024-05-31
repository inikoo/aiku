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
            'id'                    => $outer->id,
            'product_id'            => $outer->product_id,
            'slug'                  => $outer->slug,
            'code'                  => $outer->code,
            'name'                  => $outer->name,
            'price'                 => $outer->price,
            'original_price'        => $outer->price,
            'unit'                  => $outer->unit,
            'currency'              => CurrencyResource::make($outer->product->currency),
            'state'                 => $outer->state,
            'agreed_price'          => $outer->price,
            'discount'              => 0,

        ];
    }
}
