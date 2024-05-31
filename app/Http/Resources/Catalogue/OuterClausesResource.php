<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 18 Apr 2024 09:27:56 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Catalogue;

use App\Http\Resources\Assets\CurrencyResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OuterClausesResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var RentalAgreementClause $clause */
        $clause = $this;

        return [
            'id'                                => $clause->id,
            // 'rental_id'                         => $clause->product->rental->id,
            'product_id'                        => $clause->product_id,
            'slug'                              => $clause->product->slug,
            'name'                              => $clause->product->name,
            'code'                              => $clause->product->code,
            'price'                             => $clause->product->price,
            'agreed_price'                      => $clause->agreed_price,
            'discount'                          => 0,
            // 'unit'                              => $clause->product->rental->unit,
            'currency'                          => CurrencyResource::make($clause->product->currency)
        ];
    }
}
