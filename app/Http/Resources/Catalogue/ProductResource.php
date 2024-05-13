<?php
/*
 *  Author: Jonathan lopez <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:53:15 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, inikoo
 */

namespace App\Http\Resources\Catalogue;

use App\Models\Catalogue\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Product $product */
        $product=$this;
        return [
            'slug'       => $product->slug,
            'image_id'   => $product->image_id,
            'code'       => $product->code,
            'name'       => $product->name,
            'state'      => $product->state,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
        ];
    }
}
