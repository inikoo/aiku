<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 18 Apr 2024 09:27:56 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Catalogue;

use App\Http\Resources\Assets\CurrencyResource;
use App\Models\Fulfilment\RentalAgreement;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicesResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Service $service */
        $service = $this;

        return [
            'id'                                => $service->id,
            'product_id'                        => $service->product_id,
            'slug'                              => $service->product->slug,
            'name'                              => $service->product->name,
            'code'                              => $service->product->code,
            'price'                             => $service->price,
            'unit'                              => $service->unit,
            'currency'                          => CurrencyResource::make($service->product->currency),
            'agreed_price'                      => $service->price,
            'discount'                          => 0,
        ];
    }
}
