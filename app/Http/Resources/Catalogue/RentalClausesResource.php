<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 18 Apr 2024 09:27:56 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Catalogue;

use App\Http\Resources\Helpers\CurrencyResource;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalClausesResource extends JsonResource
{
    //TODO revamp this
    public function toArray($request): array
    {


        return [
            'id'                                => $this->id,
            'rental_id'                         => $this->product->rental->id,
            'product_id'                        => $this->product_id,
            'slug'                              => $this->product->slug,
            'name'                              => $this->product->name,
            'code'                              => $this->product->code,
            'price'                             => $this->product->rental->price,
            'agreed_price'                      => $this->agreed_price ??  $this->product->rental->price,
            'discount'                          => 0,
            'unit'                              => $this->product->rental->unit,
            'currency'                          => CurrencyResource::make($this->product->currency)
        ];
    }
}
