<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 19 Jun 2024 11:29:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Http\Resources\HasSelfCall;
use App\Models\Fulfilment\FulfilmentCustomer;
use Illuminate\Http\Resources\Json\JsonResource;

class FulfilmentCustomerAgreedPricesResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        $clauses = $this;

        return [
            'id' => $this->id,
            'asset_code' => $this->asset_code,
            'asset_name' => $this->asset_name,
            'asset_type' => $this->asset_type,
            'asset_price' => $this->asset_price,
            'asset_units' => $this->asset_units,
            'asset_unit'  => $this->asset_unit,
            'percentage_off' => $this->percentage_off
        ];
    }
}
